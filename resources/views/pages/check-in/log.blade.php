@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Bitácora de escaneos" />

    <x-common.flash />

    @php
        $allowedTotal = (int) ($totals['valid'] ?? 0);
        $deniedTotal = (int) $totals->sum() - $allowedTotal;
    @endphp

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Escaneos totales</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ (int) $totals->sum() }}</p>
        </div>

        <div class="rounded-2xl border border-success-200 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/10">
            <p class="text-xs font-medium uppercase text-success-700 dark:text-success-400">Accesos permitidos</p>
            <p class="mt-2 text-2xl font-bold text-success-700 dark:text-success-400">{{ $allowedTotal }}</p>
        </div>

        <div class="rounded-2xl border border-error-200 bg-error-50 p-4 dark:border-error-500/30 dark:bg-error-500/10">
            <p class="text-xs font-medium uppercase text-error-700 dark:text-error-400">Accesos denegados</p>
            <p class="mt-2 text-2xl font-bold text-error-700 dark:text-error-400">{{ $deniedTotal }}</p>
        </div>
    </div>

    @if ($eventTotals->isNotEmpty())
        <div class="mb-5 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Escaneos por evento</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[480px]">
                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <tr>
                            <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Evento</th>
                            <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total</th>
                            <th class="px-5 py-3 text-end text-xs font-medium uppercase text-success-700 dark:text-success-400">Permitidos</th>
                            <th class="px-5 py-3 text-end text-xs font-medium uppercase text-error-700 dark:text-error-400">Denegados</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($eventTotals as $eventTotal)
                            <tr>
                                <td class="px-5 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $eventTotal['name'] }}</td>
                                <td class="px-5 py-3 text-end text-sm text-gray-500 dark:text-gray-400">{{ $eventTotal['total'] }}</td>
                                <td class="px-5 py-3 text-end text-sm text-success-700 dark:text-success-400">{{ $eventTotal['allowed'] }}</td>
                                <td class="px-5 py-3 text-end text-sm text-error-700 dark:text-error-400">{{ $eventTotal['denied'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <form method="GET" action="{{ route('check-in.log') }}" class="mb-5 flex flex-wrap items-end gap-3">
        <x-form.select
            name="result"
            label="Resultado"
            :value="request('result')"
            :options="$results"
            placeholder="Todos"
            class="w-full sm:w-56"
        />

        <x-form.input
            name="code"
            label="Código"
            :value="request('code')"
            placeholder="Buscar por código"
            class="w-full sm:w-72"
        />

        <button
            type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            Filtrar
        </button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px]">
                <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Fecha y hora</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Resultado</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Código</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Cliente</th>
                        <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Gestor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($scans->groupBy(fn ($scan) => $scan->ticket?->event_id ?? 'unknown') as $eventScans)
                        <tr class="bg-gray-50 dark:bg-white/[0.02]">
                            <th colspan="5" class="px-5 py-3 text-start text-sm font-semibold text-gray-800 dark:text-white/90">
                                {{ $eventScans->first()->ticket?->event?->name ?? __('Unknown event') }}
                            </th>
                        </tr>
                        @foreach($eventScans as $scan)
                            <tr>
                                <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $scan->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :color="$scan->result->isAllowed() ? 'success' : 'error'" size="sm">
                                        {{ $scan->result->describe() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 font-mono text-sm text-gray-800 dark:text-white/90">
                                    {{ $scan->code }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-800 dark:text-white/90">
                                    {{ $scan->ticket?->buyer_name ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-end text-sm text-gray-500 dark:text-gray-400">
                                    {{ $scan->manager?->name ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                Todavía no hay escaneos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $scans->links() }}
    </div>
@endsection
