<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->ticketsFor($request)
            ->with(['event', 'ticketType', 'seller'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('pages.tickets.index', ['title' => 'Boletos', 'tickets' => $tickets]);
    }

    public function create(): View
    {
        $this->authorize('create', Ticket::class);

        return view('pages.tickets.create', [
            'title' => 'Registrar venta',
            'events' => $this->sellableEvents(),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = DB::transaction(fn () => Ticket::create(
            $request->ticketAttributes($request->user()->id)
        ));

        $ticket->storeBuyerPhoto($request->file('buyer_photo'));
        $ticket->storePaymentReceipt($request->file('payment_receipt'));
        $ticket->save();

        return to_route('tickets.show', $ticket)
            ->with('status', 'Venta registrada correctamente.');
    }

    public function show(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['event', 'ticketType', 'seller']);

        return view('pages.tickets.show', ['title' => 'Detalle del boleto', 'ticket' => $ticket]);
    }

    public function eventTypes(Event $event): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $earlyPhase = $event->isInEarlyPhase();

        return response()->json(
            $event->activeTicketTypes()
                ->orderBy('id')
                ->get()
                ->map(fn (TicketType $type) => [
                    'id' => $type->id,
                    'label' => $type->name.' · $'.number_format($type->priceForPhase($earlyPhase), 2),
                ])
                ->values()
                ->all()
        );
    }

    public function send(Ticket $ticket): RedirectResponse
    {
        $this->authorize('send', $ticket);

        return to_route('tickets.show', $ticket);
    }

    public function download(Ticket $ticket): RedirectResponse
    {
        $this->authorize('view', $ticket);

        return to_route('tickets.show', $ticket);
    }

    public function cancel(Ticket $ticket): RedirectResponse
    {
        $this->authorize('cancel', $ticket);

        $ticket->update(['status' => TicketStatus::Cancelled]);

        return to_route('tickets.show', $ticket)
            ->with('status', 'Boleto cancelado correctamente.');
    }

    public function cancelled(): View
    {
        $this->authorize('viewCancelled', Ticket::class);

        $tickets = Ticket::query()
            ->cancelled()
            ->with(['event', 'ticketType', 'seller'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('pages.tickets.cancelled', ['title' => 'Boletos cancelados', 'tickets' => $tickets]);
    }

    private function ticketsFor(Request $request)
    {
        return Ticket::query()
            ->when(
                ! $request->user()->isAdmin(),
                fn ($query) => $query->soldBy($request->user()->id)
            );
    }

    private function sellableEvents()
    {
        return Event::query()
            ->where('is_active', true)
            ->orderBy('starts_at')
            ->get();
    }
}
