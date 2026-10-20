<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TicketTypeController extends Controller
{
    public function index(Event $event): View
    {
        $this->authorize('viewAny', TicketType::class);

        $event->loadCount(['tickets as sold_seats_count' => fn ($query) => $query->active()]);

        $ticketTypes = $event->ticketTypes()
            ->withCount(['tickets as sold_count'])
            ->orderBy('id')
            ->get();

        return view('pages.events.prices', [
            'title' => 'Precios del evento',
            'event' => $event,
            'ticketTypes' => $ticketTypes,
        ]);
    }

    public function store(StoreTicketTypeRequest $request): RedirectResponse
    {
        TicketType::create($request->ticketTypeAttributes());

        return to_route('events.prices', $request->integer('event_id'))
            ->with('status', 'Boleto agregado correctamente.');
    }

    public function toggle(Event $event, TicketType $ticketType): RedirectResponse
    {
        $this->authorize('update', $ticketType);

        $ticketType->update(['is_active' => ! $ticketType->is_active]);

        return to_route('events.prices', $event)
            ->with('status', 'Boleto actualizado correctamente.');
    }

    public function destroy(Event $event, TicketType $ticketType): RedirectResponse
    {
        $this->authorize('delete', $ticketType);

        $ticketType->delete();

        return to_route('events.prices', $event)
            ->with('status', 'Boleto eliminado correctamente.');
    }
}
