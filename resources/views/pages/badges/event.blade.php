@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Badges for :event', ['event' => $event->name])" />

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $event->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ trans_choice(':count client with active tickets|:count clients with active tickets', $tickets->count(), ['count' => $tickets->count()]) }}
            </p>
        </div>
        <a
            href="{{ route('badges.events.print', $event) }}"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"
        >
            {{ __('Print all badges') }}
        </a>
    </div>

    <form
        method="POST"
        action="{{ route('badges.events.print-selected', $event) }}"
        target="_blank"
        x-data="{ selectedCount: 0 }"
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
    >
        @csrf
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                <input
                    type="checkbox"
                    @change="$el.form.querySelectorAll('[data-ticket-selection]').forEach((checkbox) => checkbox.checked = $el.checked); selectedCount = $el.checked ? {{ $tickets->count() }} : 0"
                    class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700"
                >
                {{ __('Select all clients') }}
            </label>
            <button
                type="submit"
                :disabled="selectedCount === 0"
                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{ __('Print selected badges') }} (<span x-text="selectedCount">0</span>)
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px]">
                <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="w-12 px-5 py-3"></th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Client') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Ticket type') }}</th>
                        <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Individual badge') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($tickets as $ticket)
                        <tr>
                            <td class="px-5 py-4">
                                <input
                                    type="checkbox"
                                    name="ticket_ids[]"
                                    value="{{ $ticket->id }}"
                                    data-ticket-selection
                                    @change="selectedCount += $el.checked ? 1 : -1"
                                    class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700"
                                >
                            </td>
                            <td class="px-5 py-4">
                                <span class="block text-sm font-medium text-gray-800 dark:text-white/90">{{ $ticket->buyer_name }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $ticket->buyer_email }}</span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $ticket->ticketType->name }}</td>
                            <td class="px-5 py-4 text-end">
                                <a
                                    href="{{ route('badges.tickets.print', $ticket) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400"
                                >
                                    {{ __('Print badge') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
@endsection
