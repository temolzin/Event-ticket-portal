<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Mail\TicketMail;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ticket::class);

        $events = $this->registeredEvents($request);

        return view('pages.tickets.index', ['title' => 'Clientes', 'events' => $events]);
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

        return view('pages.tickets.show', ['title' => 'Detalle del cliente', 'ticket' => $ticket]);
    }

    public function edit(Ticket $ticket): View
    {
        $this->authorize('update', $ticket);

        return view('pages.tickets.edit', [
            'title' => 'Editar cliente',
            'ticket' => $ticket->load(['event', 'ticketType']),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $attributes = $request->ticketAttributes();
        $ticket->fill($attributes);

        if ($request->hasFile('buyer_photo')) {
            $previousPhoto = $ticket->buyer_photo_path;
            $ticket->storeBuyerPhoto($request->file('buyer_photo'));

            if ($previousPhoto) {
                Storage::disk('local')->delete($previousPhoto);
            }
        }

        if ($request->hasFile('payment_receipt')) {
            $previousReceipt = $ticket->payment_receipt_path;
            $ticket->storePaymentReceipt($request->file('payment_receipt'));

            if ($previousReceipt) {
                Storage::disk('local')->delete($previousReceipt);
            }
        }

        $ticket->save();

        return to_route('tickets.show', $ticket)
            ->with('status', 'Datos del cliente actualizados correctamente.');
    }

    public function listEventTypes(Event $event): JsonResponse
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

    public function checkCancelledClient(Request $request): JsonResponse
    {
        $this->authorize('create', Ticket::class);
        $validated = $request->validate([
            'buyer_name' => ['nullable', 'string', 'max:255'],
            'buyer_email' => ['nullable', 'email', 'max:255'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
        ]);
        $name = $validated['buyer_name'] ?? '';
        $email = $validated['buyer_email'] ?? '';
        $eventId = $validated['event_id'] ?? null;

        $query = Ticket::query()
            ->cancelled()
            ->matchingCancelledClient($name, $email);

        if ($eventId !== null) {
            $query->where('event_id', $eventId);
        }

        return response()->json([
            'exists' => ($name !== '' || $email !== '')
                && $query->exists(),
        ]);
    }

    public function send(Ticket $ticket): RedirectResponse
    {
        $this->authorize('send', $ticket);

        try {
            Mail::to($ticket->buyer_email)->send(new TicketMail($ticket));
        } catch (Throwable $exception) {
            report($exception);

            return to_route('tickets.show', $ticket)->withErrors([
                'correo' => 'No se pudo enviar el correo. Revisa la configuración del servidor de correo.',
            ]);
        }

        $ticket->update(['status' => TicketStatus::Sent, 'sent_at' => now()]);

        return to_route('tickets.show', $ticket)
            ->with('status', 'Boleto enviado a '.$ticket->buyer_email.'.');
    }

    public function download(string $ticket): View
    {
        $encryptedId = strtr($ticket, '-_', '+/');
        $encryptedId .= str_repeat('=', (4 - strlen($encryptedId) % 4) % 4);

        try {
            $ticketId = Crypt::decryptString($encryptedId);
        } catch (DecryptException) {
            abort(404);
        }

        if (! ctype_digit($ticketId)) {
            abort(404);
        }

        $ticket = Ticket::query()->findOrFail((int) $ticketId);
        $this->authorize('view', $ticket);

        $ticket->load(['event', 'ticketType']);

        return view('pages.tickets.print', ['title' => 'Boleto', 'ticket' => $ticket]);
    }

    public function showBuyerPhoto(string $ticket): BinaryFileResponse
    {
        $ticket = $this->resolveTicketFromEncryptedKey($ticket);

        $this->authorize('view', $ticket);

        $path = $ticket->buyerPhotoPath();

        if (! $path || ! is_file($path)) {
            abort(404);
        }

        return $this->privateFileResponse($path, 'foto-'.$ticket->code.$this->resolveExtension($path));
    }

    public function downloadBuyerPhoto(string $ticket): BinaryFileResponse
    {
        $ticket = $this->resolveTicketFromEncryptedKey($ticket);

        $this->authorize('view', $ticket);

        $path = $ticket->buyerPhotoPath();

        if (! $path || ! is_file($path)) {
            abort(404);
        }

        return $this->privateFileResponse($path, 'foto-'.$ticket->code.$this->resolveExtension($path), 'attachment');
    }

    public function showPaymentReceipt(string $ticket): BinaryFileResponse
    {
        $ticket = $this->resolveTicketFromEncryptedKey($ticket);

        $this->authorize('view', $ticket);

        $path = $ticket->paymentReceiptPath();

        if (! $path || ! is_file($path)) {
            abort(404);
        }

        return $this->privateFileResponse($path, 'comprobante-'.$ticket->code.$this->resolveExtension($path));
    }

    private function resolveTicketFromEncryptedKey(string $encryptedKey): Ticket
    {
        $encryptedKey = strtr($encryptedKey, '-_', '+/');
        $encryptedKey .= str_repeat('=', (4 - strlen($encryptedKey) % 4) % 4);

        try {
            $ticketId = Crypt::decryptString($encryptedKey);
        } catch (DecryptException) {
            abort(404);
        }

        if (! ctype_digit($ticketId)) {
            abort(404);
        }

        return Ticket::query()->findOrFail((int) $ticketId);
    }

    public function cancel(Ticket $ticket): RedirectResponse
    {
        $this->authorize('cancel', $ticket);

        $ticket->update(['status' => TicketStatus::Cancelled]);

        return to_route('tickets.cancelled')
            ->with('status', 'Cliente cancelado correctamente.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->update(['status' => TicketStatus::Cancelled]);

        return to_route('tickets.cancelled')
            ->with('status', 'Cliente cancelado correctamente.');
    }

    public function reactivate(Ticket $ticket): RedirectResponse
    {
        $this->authorize('reactivate', $ticket);

        $ticket->update(['status' => TicketStatus::Generated]);

        return to_route('tickets.cancelled')
            ->with('status', 'Cliente reactivado correctamente.');
    }

    public function listCancelled(): View
    {
        $this->authorize('viewCancelled', Ticket::class);

        $tickets = Ticket::query()
            ->cancelled()
            ->with(['event', 'ticketType', 'seller'])
            ->orderBy('event_id')
            ->orderByDesc('id')
            ->paginate(15);

        return view('pages.tickets.cancelled', ['title' => 'Clientes cancelados', 'tickets' => $tickets]);
    }

    private function privateFileResponse(string $path, string $downloadName, string $disposition = 'inline'): BinaryFileResponse
    {
        return (new BinaryFileResponse($path, 200, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ], false))->setContentDisposition($disposition, $downloadName);
    }

    private function resolveExtension(string $path): string
    {
        return '.'.pathinfo($path, PATHINFO_EXTENSION);
    }

    private function registeredEvents(Request $request)
    {
        return Event::query()
            ->whereHas('tickets', fn ($query) => $query->active()->when(
                ! $request->user()->isAdmin(),
                fn ($inner) => $inner->soldBy($request->user()->id)
            ))
            ->with([
                'tickets' => fn ($query) => $query
                    ->active()
                    ->when(
                        ! $request->user()->isAdmin(),
                        fn ($inner) => $inner->soldBy($request->user()->id)
                    )
                    ->with(['ticketType', 'seller'])
                    ->orderByDesc('id'),
            ])
            ->orderByDesc('starts_at')
            ->get();
    }

    private function sellableEvents()
    {
        return Event::query()
            ->where('is_active', true)
            ->orderBy('starts_at')
            ->get();
    }
}
