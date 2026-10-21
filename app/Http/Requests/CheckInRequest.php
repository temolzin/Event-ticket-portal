<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('checkIn', Ticket::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Escribe el código del boleto.',
            'code.string' => 'El código del boleto debe ser un texto.',
            'code.max' => 'El código no puede pasar de 40 caracteres.',
        ];
    }

    public function code(): string
    {
        return (string) $this->validated()['code'];
    }
}
