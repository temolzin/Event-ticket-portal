<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'venue' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'early_quantity' => ['required', 'integer', 'min:0', 'max:100000', 'lte:capacity'],
            'is_active' => ['boolean'],
            'ticket_types' => ['nullable', 'array'],
            'ticket_types.*.name' => ['required', 'string', 'max:255'],
            'ticket_types.*.seats_per_ticket' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'ticket_types.*.early_price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'ticket_types.*.regular_price' => ['required', 'numeric', 'min:0', 'max:999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre del evento.',
            'name.max' => 'El nombre no puede pasar de 255 caracteres.',
            'description.max' => 'La descripción no puede pasar de 2000 caracteres.',
            'venue.required' => 'Escribe el lugar del evento.',
            'starts_at.required' => 'Selecciona la fecha del evento.',
            'starts_at.date' => 'La fecha del evento no es válida.',
            'capacity.required' => 'Escribe la capacidad del evento.',
            'capacity.integer' => 'La capacidad debe ser un número entero.',
            'capacity.min' => 'La capacidad debe ser al menos 1.',
            'capacity.max' => 'La capacidad es demasiado grande.',
            'early_quantity.required' => 'Escribe la cantidad de boletos con precio de preventa.',
            'early_quantity.integer' => 'La cantidad de preventa debe ser un número entero.',
            'early_quantity.min' => 'La cantidad de preventa no puede ser negativa.',
            'early_quantity.lte' => 'La cantidad de preventa no puede pasar de la capacidad.',
            'is_active.boolean' => 'El estado del evento no es válido.',
            'ticket_types.array' => 'Los precios de los boletos no son válidos.',
            'ticket_types.*.name.required' => 'Escribe el nombre del boleto.',
            'ticket_types.*.name.max' => 'El nombre del boleto no puede pasar de 255 caracteres.',
            'ticket_types.*.seats_per_ticket.integer' => 'Los lugares por boleto deben ser un número entero.',
            'ticket_types.*.seats_per_ticket.min' => 'Cada boleto debe ocupar al menos un lugar.',
            'ticket_types.*.seats_per_ticket.max' => 'La cantidad de lugares por boleto es demasiado grande.',
            'ticket_types.*.early_price.numeric' => 'El precio de preventa debe ser un número.',
            'ticket_types.*.early_price.min' => 'El precio de preventa no puede ser negativo.',
            'ticket_types.*.early_price.max' => 'El precio de preventa es demasiado grande.',
            'ticket_types.*.regular_price.required' => 'Escribe el precio normal del boleto.',
            'ticket_types.*.regular_price.numeric' => 'El precio normal debe ser un número.',
            'ticket_types.*.regular_price.min' => 'El precio normal no puede ser negativo.',
            'ticket_types.*.regular_price.max' => 'El precio normal es demasiado grande.',
        ];
    }

    public function eventAttributes(): array
    {
        return $this->safe()->only([
            'name',
            'description',
            'venue',
            'starts_at',
            'capacity',
            'early_quantity',
            'is_active',
        ]);
    }

    public function ticketTypeAttributes(): array
    {
        return collect($this->validated()['ticket_types'] ?? [])
            ->map(fn (array $type) => [
                'key' => Str::limit(Str::slug($type['name']), 30, ''),
                'name' => $type['name'],
                'seats_per_ticket' => $type['seats_per_ticket'] ?? 1,
                'early_price' => $type['early_price'] ?? null,
                'regular_price' => $type['regular_price'],
                'is_active' => true,
            ])
            ->unique('key')
            ->values()
            ->all();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
