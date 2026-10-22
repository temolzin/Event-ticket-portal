@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Client badges')" />

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ __('Client badges') }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Choose an event to print every badge, select clients, or print one badge at a time.') }}
        </p>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        @forelse($events as $event)
            <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $event->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $event->starts_at->translatedFormat('d M Y, H:i') }} · {{ $event->venue }}
                        </p>
                    </div>
                    <x-ui.badge color="light" size="sm">
                        {{ trans_choice(':count client|:count clients', $event->badge_tickets_count, ['count' => $event->badge_tickets_count]) }}
                    </x-ui.badge>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a
                        href="{{ route('badges.events.print', $event) }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"
                    >
                        {{ __('Print all badges') }}
                    </a>
                    <a
                        href="{{ route('badges.events.show', $event) }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                    >
                        {{ __('Choose clients') }}
                    </a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center dark:border-gray-700 dark:bg-white/[0.03] xl:col-span-2">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('No clients available for badges') }}</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Clients with active tickets will appear here.') }}
                </p>
            </div>
        @endforelse
    </div>
@endsection
