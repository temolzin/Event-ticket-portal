@php
    $method = $method ?? null;
    $user = $user ?? null;
    $passwordRequired = $user === null;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    {{ $method ? method_field($method) : '' }}

    <x-common.component-card :title="__('User details')">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-form.input
                name="name"
                :label="__('Name')"
                :value="$user->name ?? null"
                required
                autocomplete="name"
            />

            <x-form.select
                name="role"
                :label="__('Role')"
                :value="$user->role->value ?? null"
                :options="$roles"
                :placeholder="__('Choose a role')"
                required
            />

            <div class="sm:col-span-2">
                <x-form.input
                    name="email"
                    type="email"
                    :label="__('Email')"
                    :value="$user->email ?? null"
                    required
                    autocomplete="email"
                />
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    {{ __('Password') }}
                    <span class="text-error-500">{{ $passwordRequired ? '*' : '' }}</span>
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    {{ $passwordRequired ? 'required' : '' }}
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="{{ $passwordRequired ? __('At least 8 characters') : __('Leave blank to keep the current password') }}"
                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border {{ $errors->has('password') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }} bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                >
                @error('password')
                    <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    {{ __('Confirm password') }}
                    <span class="text-error-500">{{ $passwordRequired ? '*' : '' }}</span>
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    {{ $passwordRequired ? 'required' : '' }}
                    minlength="8"
                    autocomplete="new-password"
                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                >
            </div>
        </div>
        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
            {{ $passwordRequired ? __('A password is required when creating a user.') : __('The password is optional when editing a user.') }}
        </p>
    </x-common.component-card>

    <div class="flex flex-wrap justify-end gap-3">
        <a
            href="{{ route('users.index') }}"
            class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-5 py-3.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
        >
            {{ __('Cancel') }}
        </a>
        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            {{ $submitLabel }}
        </button>
    </div>
</form>
