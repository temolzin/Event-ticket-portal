@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Users')" />

    <x-common.flash />

    <div class="mb-5 flex justify-end">
        <a
            href="{{ route('users.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            {{ __('Create user') }}
        </a>
    </div>

    <form method="GET" action="{{ route('users.index') }}" class="mb-5 flex flex-wrap items-end gap-3">
        <x-form.input
            name="search"
            :label="__('Search users')"
            :value="request('search')"
            :placeholder="__('Name or email')"
            class="w-full sm:w-72"
        />

        <x-form.select
            name="role"
            :label="__('Role')"
            :value="request('role')"
            :options="$roles"
            :placeholder="__('All roles')"
            class="w-full sm:w-56"
        />

        <button
            type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            {{ __('Filter') }}
        </button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[880px]">
                <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Name') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Email') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Role') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Created') }}</th>
                        <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($users as $user)
                        @php
                            $isCurrentUser = $user->id === auth()->id();
                            $hasHistory = $user->tickets_count > 0 || $user->scans_count > 0;
                            $isLastAdmin = $user->isAdmin() && $adminCount === 1;
                            $deleteBlocked = $isCurrentUser || $hasHistory || $isLastAdmin;
                        @endphp
                        <tr>
                            <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $user->name }}
                                <span class="ms-1 text-xs font-normal text-gray-500 dark:text-gray-400">
                                    {{ $isCurrentUser ? '(' . __('You') . ')' : '' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                            <td class="px-5 py-4">
                                <x-ui.badge :color="$user->isAdmin() ? 'warning' : ($user->isManager() ? 'success' : 'primary')" size="sm">
                                    {{ $roles[$user->role->value] }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('users.edit', $user) }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                                    >
                                        {{ __('Edit') }}
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('users.destroy', $user) }}"
                                        x-data
                                        @submit.prevent="confirm(@js(__('Are you sure you want to delete this user?'))) && $el.submit()"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            @disabled($deleteBlocked)
                                            title="{{ $isCurrentUser ? __('You cannot delete your own account.') : ($hasHistory ? __('This user has sales or check-in history and cannot be deleted.') : ($isLastAdmin ? __('The last administrator cannot be deleted.') : __('Delete user'))) }}"
                                            class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-xs font-medium transition {{ $deleteBlocked ? 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-600' : 'bg-error-50 text-error-500 hover:bg-error-100 dark:bg-error-500/15 dark:text-error-500' }}"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No users match your search.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $users->links() }}
    </div>
@endsection
