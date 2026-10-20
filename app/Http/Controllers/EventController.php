<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Event::class);

        $events = Event::query()
            ->withCount(['tickets as sold_seats_count' => fn ($query) => $query->active()])
            ->orderByDesc('starts_at')
            ->paginate(15);

        return view('pages.events.index', ['title' => 'Eventos', 'events' => $events]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('pages.events.create', ['title' => 'Nuevo evento']);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        Event::create($request->eventAttributes());

        return to_route('events.index')->with('status', 'Evento creado correctamente.');
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->loadCount(['tickets as sold_seats_count' => fn ($query) => $query->active()]);

        return view('pages.events.show', ['title' => 'Detalle del evento', 'event' => $event]);
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('pages.events.edit', ['title' => 'Editar evento', 'event' => $event]);
    }

    public function update(StoreEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $event->update($request->eventAttributes());

        return to_route('events.show', $event)->with('status', 'Evento actualizado correctamente.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return to_route('events.index')->with('status', 'Evento eliminado correctamente.');
    }
}
