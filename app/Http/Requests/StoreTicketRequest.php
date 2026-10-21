<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Ticket::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'event_id' => [
                'required',
                'integer',
                Rule::exists('events', 'id')->where('is_active', true),
            ],
            'ticket_type_id' => [
                'required',
                'integer',
                Rule::exists('ticket_types', 'id')
                    ->where('event_id', $this->integer('event_id'))
                    ->where('is_active', true),
            ],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_email' => ['required', 'email', 'max:255'],
            'buyer_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'payment_receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_id.required' => 'Selecciona el evento.',
            'event_id.exists' => 'El evento no está disponible para venta.',
            'ticket_type_id.required' => 'Selecciona el tipo de boleto.',
            'ticket_type_id.exists' => 'El tipo de boleto no está disponible para este evento.',
            'buyer_name.required' => 'Escribe el nombre del cliente.',
            'buyer_name.max' => 'El nombre no puede pasar de 255 caracteres.',
            'buyer_email.required' => 'Escribe el correo del cliente.',
            'buyer_email.email' => 'El correo del cliente no es válido.',
            'buyer_email.max' => 'El correo no puede pasar de 255 caracteres.',
            'buyer_photo.required' => 'Sube la foto del cliente.',
            'buyer_photo.image' => 'La foto del cliente debe ser una imagen.',
            'buyer_photo.mimes' => 'La foto debe ser jpg, png o webp.',
            'buyer_photo.max' => 'La foto no puede pesar más de 5 MB.',
            'payment_receipt.required' => 'Sube el comprobante de pago.',
            'payment_receipt.mimes' => 'El comprobante debe ser jpg, png, webp o pdf.',
            'payment_receipt.max' => 'El comprobante no puede pesar más de 10 MB.',
        ];
    }

    public function event(): Event
    {
        return Event::query()->findOrFail($this->integer('event_id'));
    }

    public function ticketType(): TicketType
    {
        return TicketType::query()->findOrFail($this->integer('ticket_type_id'));
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $event = Event::query()
                ->whereKey($this->integer('event_id'))
                ->where('is_active', true)
                ->first();
            $ticketType = TicketType::query()
                ->whereKey($this->integer('ticket_type_id'))
                ->where('event_id', $this->integer('event_id'))
                ->where('is_active', true)
                ->first();

            $validator->errors()->addIf(
                $event && $ticketType && ! $event->hasAvailableSeats($ticketType->seats_per_ticket),
                'event_id',
                'Este evento no tiene suficientes lugares disponibles para ese boleto.'
            );
        });
    }

    public function ticketAttributes(int $sellerId): array
    {
        $event = $this->event();
        $ticketType = $this->ticketType();

        return [
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'seller_id' => $sellerId,
            'buyer_name' => $this->string('buyer_name')->trim()->value(),
            'buyer_email' => $this->string('buyer_email')->trim()->lower()->value(),
            'price' => $ticketType->priceForPhase($event->isInEarlyPhase()),
            'code' => Ticket::generateCode(),
        ];
    }
}
