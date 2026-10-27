@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Clientes cancelados" />

    <x-common.flash />

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px]">
                <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Cliente</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Boleto</th>
                        <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Precio</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Vendedor</th>
                        <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tickets->groupBy('event_id') as $eventTickets)
                        <tr class="bg-gray-50 dark:bg-white/[0.02]">
                            <th colspan="5" class="px-5 py-3 text-start text-sm font-semibold text-gray-800 dark:text-white/90">
                                {{ $eventTickets->first()->event->name }}
                            </th>
                        </tr>
                        @foreach($eventTickets as $ticket)
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
                                <td class="px-5 py-4 text-end">
                                    <a
                                        href="{{ route('tickets.show', $ticket) }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                                    >
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                Todavía no hay boletos cancelados.
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
