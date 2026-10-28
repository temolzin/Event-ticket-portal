@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Permissions by role')" />

    <x-common.flash />

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Reference of the permissions each role has in the system.') }}
        </p>
        <a
            href="{{ route('users.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
        >
            {{ __('Back to users') }}
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px]">
                <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                            {{ __('Permission') }}
                        </th>
                        @foreach ($roles as $role)
                            <th class="px-5 py-3 text-center text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                {{ $role['name'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($matrix as $group)
                        <tr class="bg-gray-50 dark:bg-white/[0.02]">
                            <td colspan="{{ count($roles) + 1 }}" class="px-5 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                {{ $group['title'] }}
                            </td>
                        </tr>
                        @foreach ($group['items'] as $item)
                            <tr>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $item['label'] }}
                                </td>
                                @foreach ($roles as $role)
                                    <td class="px-5 py-3 text-center">
                                        @if ($item[$role['key']])
                                            <span class="inline-flex size-6 items-center justify-center rounded-full bg-success-100 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </span>
                                        @else
                                            <span class="inline-flex size-6 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500">
                                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection