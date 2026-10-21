@php
    $allowed = $scan->result->isAllowed();
    $ticket = $scan->ticket;
@endphp

<div class="overflow-hidden rounded-2xl border {{ $allowed ? 'border-success-200 bg-success-50 dark:border-success-500/30 dark:bg-success-500/10' : 'border-error-200 bg-error-50 dark:border-error-500/30 dark:bg-error-500/10' }}">
    <div class="px-6 py-8 text-center">
        <span class="mx-auto flex size-20 items-center justify-center rounded-full {{ $allowed ? 'bg-success-500' : 'bg-error-500' }} text-white">
            @if($allowed)
                <svg class="size-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            @else
                <svg class="size-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            @endif
        </span>

        <h2 class="mt-5 text-2xl font-bold {{ $allowed ? 'text-success-700 dark:text-success-400' : 'text-error-700 dark:text-error-400' }}">
            {{ $allowed ? 'Acceso permitido' : 'Acceso denegado' }}
        </h2>

        <p class="mt-2 text-sm {{ $allowed ? 'text-success-700 dark:text-success-300' : 'text-error-700 dark:text-error-300' }}">
            {{ $scan->result->reason() }}
        </p>

        <x-ui.badge class="mt-4" :color="$allowed ? 'success' : 'error'" variant="light" size="sm">
            {{ $scan->result->describe() }}
        </x-ui.badge>
    </div>

    <div class="space-y-3 border-t border-gray-900/5 px-6 py-5 text-sm dark:border-white/10">
        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500 dark:text-gray-400">Código</span>
            <span class="text-end font-mono font-medium text-gray-800 dark:text-white/90">{{ $scan->code }}</span>
        </div>

        @if($ticket)
            <div class="flex items-start justify-between gap-4">
                <span class="text-gray-500 dark:text-gray-400">Cliente</span>
                <span class="text-end font-medium text-gray-800 dark:text-white/90">{{ $ticket->buyer_name }}</span>
            </div>

            <div class="flex items-start justify-between gap-4">
                <span class="text-gray-500 dark:text-gray-400">Evento</span>
                <span class="text-end font-medium text-gray-800 dark:text-white/90">{{ $ticket->event->name }}</span>
            </div>

            <div class="flex items-start justify-between gap-4">
                <span class="text-gray-500 dark:text-gray-400">Tipo de boleto</span>
                <span class="text-end font-medium text-gray-800 dark:text-white/90">{{ $ticket->ticketType->name }}</span>
            </div>
        @endif

        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500 dark:text-gray-400">Fecha y hora</span>
            <span class="text-end font-medium text-gray-800 dark:text-white/90">{{ $scan->created_at->format('d/m/Y H:i') }}</span>
        </div>

        <div class="flex items-start justify-between gap-4">
            <span class="text-gray-500 dark:text-gray-400">Escaneado por</span>
            <span class="text-end font-medium text-gray-800 dark:text-white/90">{{ $scan->manager?->name }}</span>
        </div>
    </div>

    <div class="border-t border-gray-900/5 px-6 py-4 dark:border-white/10">
        <a
            href="{{ route('check-in.scan') }}"
            data-check-in-reset
            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            Escanear otro boleto
        </a>
    </div>
</div>
