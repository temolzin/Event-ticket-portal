<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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

        return view('pages.events.create', [
            'title' => 'Nuevo evento',
            'ticketRows' => $this->emptyTicketRows(),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = DB::transaction(function () use ($request) {
            $event = Event::create($request->eventAttributes());

            $event->ticketTypes()->createMany($request->ticketTypeAttributes());

            return $event;
        });

        return to_route('events.index')->with('status', 'Evento creado correctamente.');
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->loadCount(['tickets as sold_seats_count' => fn ($query) => $query->active()]);
        $event->load('ticketTypes');

        return view('pages.events.show', ['title' => 'Detalle del evento', 'event' => $event]);
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $event->load('ticketTypes');

        return view('pages.events.edit', [
            'title' => 'Editar evento',
            'event' => $event,
            'ticketRows' => $this->emptyTicketRows(),
        ]);
    }

    public function update(StoreEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        DB::transaction(function () use ($request, $event) {
            $event->update($request->eventAttributes());

            $existingKeys = $event->ticketTypes()->pluck('key')->all();

            collect($request->ticketTypeAttributes())
                ->reject(fn (array $type) => in_array($type['key'], $existingKeys, true))
                ->each(fn (array $type) => $event->ticketTypes()->create($type));
        });

        return to_route('events.show', $event)->with('status', 'Evento actualizado correctamente.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return to_route('events.index')->with('status', 'Evento eliminado correctamente.');
    }

    private function emptyTicketRows(): array
    {
        return [
            ['name' => '', 'early_price' => '', 'regular_price' => ''],
        ];
    }
}
