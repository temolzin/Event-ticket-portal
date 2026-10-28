@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Search results')" />

    <div class="mb-5">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Search for') }} <span class="font-medium text-gray-800 dark:text-white/90">“{{ $query }}”</span>
        </p>
    </div>

    @if($query === '')
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
            {{ __('Enter a search term to find events, clients, or users.') }}
        </div>
    @elseif($events->isEmpty() && $tickets->isEmpty() && $users->isEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
            {{ __('No results found for :query', ['query' => $query]) }}
        </div>
    @else
        <div class="space-y-5">
            @if($events->isNotEmpty())
                <x-common.component-card :title="__('Events')">
                    <div class="space-y-3">
                        @foreach($events as $event)
                            <a
                                href="{{ route('events.show', $event) }}"
                                class="block rounded-xl border border-gray-100 p-4 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.03]"
                            >
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $event->name }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $event->venue }} · {{ $event->starts_at->translatedFormat('d M Y, H:i') }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                </x-common.component-card>
            @endif

            @if($tickets->isNotEmpty())
                <x-common.component-card :title="__('Clients')">
                    <div class="space-y-3">
                        @foreach($tickets as $ticket)
                            <a
                                href="{{ route('tickets.show', $ticket) }}"
                                class="block rounded-xl border border-gray-100 p-4 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.03]"
                            >
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $ticket->buyer_name }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $ticket->buyer_email }} · {{ $ticket->event->name }} · {{ $ticket->code }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                </x-common.component-card>
            @endif

            @if($users->isNotEmpty())
                <x-common.component-card :title="__('Users')">
                    <div class="space-y-3">
                        @foreach($users as $user)
                            <a
                                href="{{ route('users.edit', $user) }}"
                                class="block rounded-xl border border-gray-100 p-4 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.03]"
                            >
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $user->email }} · {{ __($user->role->describe()) }}</p>
                            </a>
                        @endforeach
                    </div>
                </x-common.component-card>
            @endif
        </div>
    @endif
@endsection
