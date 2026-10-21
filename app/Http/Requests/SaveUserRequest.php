<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $creating = $this->isMethod('POST');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user instanceof User ? $user->id : null),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => [$creating ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Enter a name for the user.'),
            'name.max' => __('The user name may not be greater than 255 characters.'),
            'email.required' => __('Enter an email address for the user.'),
            'email.email' => __('Enter a valid email address.'),
            'email.unique' => __('That email address is already in use.'),
            'role.required' => __('Choose a role for the user.'),
            'role.enum' => __('Choose a valid user role.'),
            'password.required' => __('Enter a password for the user.'),
            'password.min' => __('The password must be at least 8 characters.'),
            'password.confirmed' => __('The password confirmation does not match.'),
        ];
    }

    public function getUserAttributes(): array
    {
        $attributes = $this->safe()->only(['name', 'email', 'role']);

        return $this->filled('password')
            ? array_merge($attributes, ['password' => $this->validated('password')])
            : $attributes;
    }
}
