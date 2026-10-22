@props(['pageTitle' => 'Page'])

@php
    $routeName = request()->route()?->getName();
    $event = request()->route('event');
    $ticket = request()->route('ticket');
    $ancestors = match ($routeName) {
        'events.create' => [
            ['label' => __('Events'), 'url' => route('events.index')],
        ],
        'events.show' => [
            ['label' => __('Events'), 'url' => route('events.index')],
        ],
        'events.edit', 'events.prices' => [
            ['label' => __('Events'), 'url' => route('events.index')],
            ['label' => $event?->name ?? __('Event details'), 'url' => route('events.show', $event)],
        ],
        'users.create', 'users.edit' => [
            ['label' => __('Users'), 'url' => route('users.index')],
        ],
        'tickets.create', 'tickets.show', 'tickets.cancelled' => [
            ['label' => __('Clients'), 'url' => route('tickets.index')],
        ],
        'tickets.edit' => [
            ['label' => __('Clients'), 'url' => route('tickets.index')],
            ['label' => $ticket?->buyer_name ?? __('Client'), 'url' => route('tickets.show', $ticket)],
        ],
        'badges.events.show' => [
            ['label' => __('Badges'), 'url' => route('badges.index')],
        ],
        'check-in.result' => [
            ['label' => __('Check-in'), 'url' => route('check-in.scan')],
        ],
        'check-in.log' => [
            ['label' => __('Check-in'), 'url' => route('check-in.scan')],
        ],
        'profile' => [],
        default => [],
    };
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
        {{ $pageTitle }}
    </h2>
    <nav aria-label="{{ __('Breadcrumb') }}">
        <ol class="flex flex-wrap items-center gap-1.5">
            @if($routeName !== 'dashboard')
                <li>
                    <a
                        class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                        href="{{ route('dashboard') }}"
                    >
                        {{ __('Home') }}
                        <svg
                            class="stroke-current rtl:rotate-180"
                            width="17"
                            height="16"
                            viewBox="0 0 17 16"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                                stroke=""
                                stroke-width="1.2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </a>
                </li>
            @endif
            @foreach($ancestors as $ancestor)
                <li class="flex items-center gap-1.5">
                    <svg
                        class="stroke-current text-gray-400 rtl:rotate-180 dark:text-gray-500"
                        width="17"
                        height="16"
                        viewBox="0 0 17 16"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <path
                            d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                            stroke=""
                            stroke-width="1.2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <a
                        class="text-sm text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400"
                        href="{{ $ancestor['url'] }}"
                    >
                        {{ $ancestor['label'] }}
                    </a>
                </li>
            @endforeach
            <li class="text-sm text-gray-800 dark:text-white/90" aria-current="page">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>
</div>
