<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $query = trim($validated['q'] ?? '');

        $events = collect();
        $tickets = collect();
        $users = collect();

        if ($query !== '') {
            if (Gate::allows('viewAny', Event::class)) {
                $events = Event::query()
                    ->where(function ($matches) use ($query) {
                        $matches->where('name', 'like', "%{$query}%")
                            ->orWhere('venue', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    })
                    ->orderByDesc('starts_at')
                    ->limit(10)
                    ->get();
            }

            if (Gate::allows('viewAny', Ticket::class)) {
                $tickets = Ticket::query()
                    ->with(['event', 'ticketType'])
                    ->where(function ($matches) use ($query) {
                        $matches->where('buyer_name', 'like', "%{$query}%")
                            ->orWhere('buyer_email', 'like', "%{$query}%")
                            ->orWhere('code', 'like', "%{$query}%")
                            ->orWhereHas('event', fn ($event) => $event->where('name', 'like', "%{$query}%"));
                    })
                    ->when(
                        ! $request->user()->isAdmin(),
                        fn ($matches) => $matches->where('seller_id', $request->user()->id)
                    )
                    ->orderByDesc('id')
                    ->limit(10)
                    ->get();
            }

            if (Gate::allows('viewAny', User::class)) {
                $users = User::query()
                    ->where(fn ($matches) => $matches
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%"))
                    ->orderBy('name')
                    ->limit(10)
                    ->get();
            }
        }

        return view('pages.search', [
            'title' => __('Search results'),
            'query' => $query,
            'events' => $events,
            'tickets' => $tickets,
            'users' => $users,
        ]);
    }
}
