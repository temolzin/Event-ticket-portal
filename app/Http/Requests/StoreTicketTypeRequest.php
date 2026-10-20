<?php

namespace App\Http\Requests;

use App\Models\TicketType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreTicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TicketType::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'early_price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'regular_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'event_id' => [
                'required',
                'integer',
                Rule::exists('events', 'id'),
            ],
            'key' => [
                'required',
                'string',
                'max:30',
                Rule::unique('ticket_types', 'key')->where('event_id', $this->integer('event_id')),
            ],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre del boleto.',
            'name.max' => 'El nombre no puede pasar de 255 caracteres.',
            'early_price.numeric' => 'El precio de preventa debe ser un número.',
            'early_price.min' => 'El precio de preventa no puede ser negativo.',
            'early_price.max' => 'El precio de preventa es demasiado grande.',
            'regular_price.required' => 'Escribe el precio normal del boleto.',
            'regular_price.numeric' => 'El precio normal debe ser un número.',
            'regular_price.min' => 'El precio normal no puede ser negativo.',
            'regular_price.max' => 'El precio normal es demasiado grande.',
            'event_id.required' => 'Selecciona el evento.',
            'event_id.exists' => 'El evento seleccionado no existe.',
            'key.unique' => 'Ya existe un boleto con ese nombre en este evento.',
        ];
    }

    public function ticketTypeAttributes(): array
    {
        return [
            'event_id' => $this->integer('event_id'),
            'key' => $this->resolveKey(),
            'name' => $this->string('name')->trim()->value(),
            'early_price' => $this->filled('early_price') ? $this->input('early_price') : null,
            'regular_price' => $this->input('regular_price'),
            'is_active' => $this->boolean('is_active'),
        ];
    }

    public function resolveKey(): string
    {
        return Str::limit(Str::slug($this->string('name')->value()), 30, '');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'key' => $this->resolveKey(),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
