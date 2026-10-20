<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Escribe el nombre del evento.'),
            'name.max' => __('El nombre no puede pasar de 255 caracteres.'),
            'description.max' => __('La descripción no puede pasar de 2000 caracteres.'),
            'venue.required' => __('Escribe el lugar del evento.'),
            'starts_at.required' => __('Selecciona la fecha del evento.'),
            'starts_at.date' => __('La fecha del evento no es válida.'),
            'capacity.required' => __('Escribe la capacidad del evento.'),
            'capacity.integer' => __('La capacidad debe ser un número entero.'),
            'capacity.min' => __('La capacidad debe ser al menos 1.'),
            'capacity.max' => __('La capacidad es demasiado grande.'),
            'early_quantity.required' => __('Escribe la cantidad de boletos con precio de preventa.'),
            'early_quantity.integer' => __('La cantidad de preventa debe ser un número entero.'),
            'early_quantity.min' => __('La cantidad de preventa no puede ser negativa.'),
            'early_quantity.lte' => __('La cantidad de preventa no puede pasar de la capacidad.'),
            'is_active.boolean' => __('El estado del evento no es válido.'),
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
