@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Dashboard')" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                {{ __('Welcome, :name', ['name' => $viewer->name]) }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Here is your event portal overview.') }}
            </p>
        </div>
        <a
            href="{{ $primaryAction['url'] }}"
            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            {{ $primaryAction['label'] }}
        </a>
    </div>

    <section class="mb-8">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Active events') }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Sales, remaining capacity, and check-in progress for upcoming events.') }}
                </p>
            </div>
            <x-ui.badge color="light" size="sm">
                {{ trans_choice(':count event|:count events', $events->count(), ['count' => $events->count()]) }}
            </x-ui.badge>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @forelse($events as $eventCard)
                @php
                    $event = $eventCard['event'];
                @endphp
                <article
                    data-event-id="{{ $event->id }}"
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
                >
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $event->name }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $event->venue }}
                                <span class="mx-1 text-gray-300 dark:text-gray-600">·</span>
                                {{ $event->starts_at->translatedFormat('d M Y, H:i') }}
                            </p>
                        </div>
                        <x-ui.badge color="success" size="sm">{{ __('Active') }}</x-ui.badge>
                    </div>

                    <div class="mt-5">
                        <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Event occupancy') }}</span>
                            <span class="text-gray-500 dark:text-gray-400">
                                {{ number_format($eventCard['sold']) }} / {{ number_format($event->capacity) }}
                                <span class="ms-1">({{ $eventCard['salesPercentage'] }}%)</span>
                            </span>
                        </div>
                        <progress
                            class="h-2.5 w-full appearance-none overflow-hidden rounded-full bg-gray-100 text-brand-500 accent-brand-500 [&::-moz-progress-bar]:rounded-full [&::-moz-progress-bar]:bg-brand-500 [&::-webkit-progress-bar]:rounded-full [&::-webkit-progress-bar]:bg-gray-100 [&::-webkit-progress-value]:rounded-full [&::-webkit-progress-value]:bg-brand-500 dark:bg-gray-800 dark:[&::-webkit-progress-bar]:bg-gray-800"
                            role="progressbar"
                            aria-label="{{ __('Occupied seats') }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="{{ $eventCard['salesPercentage'] }}"
                            value="{{ $eventCard['salesPercentage'] }}"
                            max="100"
                        ></progress>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach($eventCard['metrics'] as $eventMetric)
                            @php
                                $eventMetricTone = match ($eventMetric['tone']) {
                                    'blue' => 'text-blue-600 dark:text-blue-400',
                                    'success' => 'text-success-600 dark:text-success-400',
                                    'warning' => 'text-warning-600 dark:text-warning-400',
                                    'error' => 'text-error-600 dark:text-error-400',
                                    default => 'text-brand-600 dark:text-brand-400',
                                };
                            @endphp
                            <div
                                data-event-metric="{{ $eventMetric['key'] }}" data-value="{{ $eventMetric['value'] }}"
                                class="rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]"
                            >
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $eventMetric['label'] }}</p>
                                <p class="mt-1 text-xl font-semibold {{ $eventMetricTone }}">
                                    {{ number_format($eventMetric['value']) }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <a
                            href="{{ $eventCard['action']['url'] }}"
                            class="inline-flex items-center gap-2 text-sm font-medium text-brand-600 transition hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                        >
                            {{ $eventCard['action']['label'] }}
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center dark:border-gray-700 dark:bg-white/[0.03] xl:col-span-2">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('No active events yet') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Active events will appear here with their sales and check-in progress.') }}
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    <section>
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $activityTitle }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $isSeller ? __('Your latest ticket activity.') : __('The latest access attempts recorded in the portal.') }}
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px]">
                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <tr>
                            <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Activity') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Event') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ $isSeller ? __('Buyer') : __('Scanned by') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Date and time') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($activity as $activityItem)
                            @php
                                $activityTone = match ($activityItem['tone']) {
                                    'success' => 'success',
                                    'blue' => 'primary',
                                    'warning' => 'warning',
                                    default => 'error',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ $activityItem['url'] }}"
                                        class="font-mono text-sm font-medium text-brand-600 hover:underline dark:text-brand-400"
                                    >
                                        {{ $activityItem['detail'] }}
                                    </a>
                                    <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $activityItem['reference'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $activityItem['event'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $activityItem['actor'] }}</td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :color="$activityTone" size="sm">{{ $activityItem['status'] }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-end text-sm text-gray-500 dark:text-gray-400">
                                    {{ $activityItem['occurredAt']->translatedFormat('d M Y, H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ $isSeller ? __('No recent ticket activity.') : __('No recent check-in activity.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4 text-end dark:border-gray-800">
                <a
                    href="{{ $isSeller ? route('tickets.index') : route('check-in.log') }}"
                    class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400"
                >
                    {{ $isSeller ? __('View your tickets') : __('View scan log') }}
                </a>
            </div>
        </div>
    </section>
@endsection
