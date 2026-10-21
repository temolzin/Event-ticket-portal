@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Boletos" />

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

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px]">
                <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Cliente</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Evento</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Boleto</th>
                        <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Precio</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Vendedor</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</th>
                        <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="px-5 py-4 text-sm">
                                <span class="block font-medium text-gray-800 dark:text-white/90">{{ $ticket->buyer_name }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $ticket->buyer_email }}</span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $ticket->event->name }}
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                Todavía no hay boletos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $tickets->links() }}
    </div>
@endsection
