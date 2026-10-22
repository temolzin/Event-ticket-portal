@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Clientes" />

    <x-common.flash />

    <div class="mb-5 flex justify-end">
        <a
            href="{{ route('tickets.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 4.16669V15.8334M4.16669 10H15.8334" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Registrar venta
        </a>
    </div>

    <div class="space-y-4">
        @forelse($events as $event)
            <div
                x-data="{ open: false }"
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <button
                    type="button"
                    @click="open = ! open"
                    class="flex w-full flex-wrap items-center justify-between gap-4 px-5 py-4 text-start transition hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                >
                    <div class="flex items-center gap-4">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition dark:bg-white/5 dark:text-gray-400"
                            :class="open && 'rotate-180'"
                        >
                            <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $event->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $event->starts_at->translatedFormat('d M Y, H:i') }} · {{ $event->venue }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $event->tickets->count() === 1 ? '1 cliente registrado' : $event->tickets->count() . ' clientes registrados' }}
                        </span>
                        <x-ui.badge :color="$event->tickets->count() > 0 ? 'success' : 'light'" size="sm">
                            {{ $event->tickets->count() }}
                        </x-ui.badge>
                    </div>
                </button>

                <div x-show="open" x-cloak class="border-t border-gray-100 dark:border-gray-800">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[880px]">
                            <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Cliente</th>
                                    <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Boleto</th>
                                    <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Precio</th>
                                    <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Vendedor</th>
                                    <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</th>
                                    <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($event->tickets as $ticket)
                                    <tr>
                                        <td class="px-5 py-4 text-sm">
                                            <span class="block font-medium text-gray-800 dark:text-white/90">{{ $ticket->buyer_name }}</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $ticket->buyer_email }}</span>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $ticket->ticketType->name }}
                                        </td>
                                        <td class="px-5 py-4 text-end text-sm text-gray-500 dark:text-gray-400">
                                            ${{ number_format((float) $ticket->price, 2) }}
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $ticket->seller->name }}
                                        </td>
                                        <td class="px-5 py-4">
                                            <x-ui.badge
                                                :color="$ticket->status->value === 'cancelled' ? 'error' : 'success'"
                                                size="sm"
                                            >
                                                {{ $ticket->status->describe() }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-5 py-4 text-end">
                                            <a
                                                href="{{ route('tickets.show', $ticket) }}"
                                                class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                                            >
                                                Ver
                                            </a>
                                            @can('update', $ticket)
                                                <a
                                                    href="{{ route('tickets.edit', $ticket) }}"
                                                    class="ms-1 inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                                                >
                                                    Editar
                                                </a>
                                            @endcan
                                            @can('cancel', $ticket)
                                                <form
                                                    method="POST"
                                                    action="{{ route('tickets.destroy', $ticket) }}"
                                                    class="inline"
                                                    onsubmit="return confirm('¿Cancelar este boleto? El cliente aparecerá en Clientes cancelados.');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="ms-1 inline-flex items-center justify-center rounded-lg border border-error-200 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/30 dark:text-error-400 dark:hover:bg-error-500/10"
                                                    >
                                                        Cancelar
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-16 text-center dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Todavía no hay clientes registrados.
                </p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                    Registra una venta para ver aquí los clientes agrupados por evento.
                </p>
            </div>
        @endforelse
    </div>
@endsection
