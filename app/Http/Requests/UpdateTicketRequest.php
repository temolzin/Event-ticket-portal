<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket && ($this->user()?->can('update', $ticket) ?? false);
    }

    public function rules(): array
    {
        return [
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_email' => ['required', 'email', 'max:255'],
            'buyer_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'payment_receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'buyer_name.required' => 'Escribe el nombre del cliente.',
            'buyer_name.max' => 'El nombre no puede pasar de 255 caracteres.',
            'buyer_email.required' => 'Escribe el correo del cliente.',
            'buyer_email.email' => 'El correo del cliente no es válido.',
            'buyer_email.max' => 'El correo no puede pasar de 255 caracteres.',
            'buyer_photo.image' => 'La foto del cliente debe ser una imagen.',
            'buyer_photo.mimes' => 'La foto debe ser jpg, png o webp.',
            'buyer_photo.max' => 'La foto no puede pesar más de 5 MB.',
            'payment_receipt.mimes' => 'El comprobante debe ser jpg, png, webp o pdf.',
            'payment_receipt.max' => 'El comprobante no puede pesar más de 10 MB.',
        ];
    }

    public function ticketAttributes(): array
    {
        return [
            'buyer_name' => $this->string('buyer_name')->trim()->value(),
            'buyer_email' => $this->string('buyer_email')->trim()->lower()->value(),
        ];
    }
}
