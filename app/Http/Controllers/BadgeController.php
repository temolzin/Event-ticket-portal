<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ticket::class);

        $viewer = $request->user();
        $events = Event::query()
            ->whereHas('tickets', fn (Builder $ticketQuery) => $ticketQuery->active())
            ->withCount([
                'tickets as badge_tickets_count' => fn (Builder $ticketQuery) => $ticketQuery->active(),
            ])
            ->orderByDesc('starts_at')
            ->get();

        return view('pages.badges.index', [
            'title' => __('Client badges'),
            'events' => $events,
        ]);
    }

    public function showEvent(Request $request, Event $event): View
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->getEventTickets($request->user(), $event)
            ->with('ticketType')
            ->orderBy('buyer_name')
            ->get();

        abort_if($tickets->isEmpty(), 404);

        return view('pages.badges.event', [
            'title' => __('Badges for :event', ['event' => $event->name]),
            'event' => $event,
            'tickets' => $tickets,
        ]);
    }

    public function printAll(Request $request, Event $event): View
    {
        $this->authorize('viewAny', Ticket::class);

        return $this->renderBadges(
            $event,
            $this->getEventTickets($request->user(), $event)
                ->with('ticketType')
                ->orderBy('buyer_name')
                ->get()
        );
    }

    public function printSelected(Request $request, Event $event): View
    {
        $this->authorize('viewAny', Ticket::class);

        $validated = $request->validate([
            'ticket_ids' => ['required', 'array', 'min:1'],
            'ticket_ids.*' => ['required', 'integer', 'distinct'],
        ]);
        $ticketIds = $validated['ticket_ids'];
        $tickets = $this->getEventTickets($request->user(), $event)
            ->whereKey($ticketIds)
            ->with('ticketType')
            ->orderBy('buyer_name')
            ->get();

        abort_unless($tickets->count() === count($ticketIds), 404);

        return $this->renderBadges($event, $tickets);
    }

    public function printTicket(string $ticket): View
    {
        $encryptedKey = strtr($ticket, '-_', '+/');
        $encryptedKey .= str_repeat('=', (4 - strlen($encryptedKey) % 4) % 4);

        try {
            $ticketId = Crypt::decryptString($encryptedKey);
        } catch (DecryptException) {
            abort(404);
        }

        if (! ctype_digit($ticketId)) {
            abort(404);
        }

        $ticket = Ticket::query()->findOrFail((int) $ticketId);

        $this->authorize('view', $ticket);
        abort_if($ticket->isCancelled(), 404);

        return $this->renderBadges(
            $ticket->event,
            collect([$ticket->load('ticketType')])
        );
    }

    private function getEventTickets(User $viewer, Event $event): Builder
    {
        return Ticket::query()
            ->where('event_id', $event->id)
            ->active();
    }

    private function renderBadges(Event $event, Collection $tickets): View
    {
        return view('pages.badges.print', [
            'title' => __('Client badges'),
            'event' => $event,
            'tickets' => $tickets,
        ]);
    }
}
