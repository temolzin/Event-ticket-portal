@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Precios del evento" />

    <x-common.flash />

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $event->name }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $event->starts_at->translatedFormat('d M Y, H:i') }} · {{ $event->venue }}
            </p>
        </div>
        <a
            href="{{ route('events.show', $event) }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
        >
            Volver al evento
        </a>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-common.component-card title="Boletos y precios">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px]">
                        <thead class="border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-3 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Boleto</th>
                                <th class="px-3 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Preventa</th>
                                <th class="px-3 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Normal</th>
                                <th class="px-3 py-3 text-center text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Vendidos</th>
                                <th class="px-3 py-3 text-center text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</th>
                                <th class="px-3 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($ticketTypes as $ticketType)
                                <tr>
                                    <td class="px-3 py-4 text-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $ticketType->name }}
                                    </td>
                                    <td class="px-3 py-4 text-end text-sm text-gray-500 dark:text-gray-400">
                                        {{ $ticketType->early_price ? '$' . number_format((float) $ticketType->early_price, 2) : '—' }}
                                    </td>
                                    <td class="px-3 py-4 text-end text-sm text-gray-500 dark:text-gray-400">
                                        ${{ number_format((float) $ticketType->regular_price, 2) }}
                                    </td>
                                    <td class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ $ticketType->sold_count }}
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <x-ui.badge :color="$ticketType->is_active ? 'success' : 'light'" size="sm">
                                            {{ $ticketType->is_active ? 'Activo' : 'Inactivo' }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <form
                                                method="POST"
                                                action="{{ route('ticket-types.toggle', [$event, $ticketType]) }}"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                                                >
                                                    {{ $ticketType->is_active ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                            <form
                                                method="POST"
                                                action="{{ route('ticket-types.destroy', [$event, $ticketType]) }}"
                                                x-data
                                                @submit.prevent="if (confirm('¿Seguro que quieres eliminar este boleto?')) $el.submit()"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    @disabled(! $ticketType->canBeDeleted())
                                                    title="{{ $ticketType->canBeDeleted() ? 'Eliminar boleto' : 'No se puede eliminar: ya tiene ventas registradas' }}"
                                                    class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-xs font-medium transition {{ $ticketType->canBeDeleted() ? 'bg-error-50 text-error-500 hover:bg-error-100 dark:bg-error-500/15 dark:text-error-500' : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-600' }}"
                                                >
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Todavía no hay boletos registrados para este evento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-common.component-card>
        </div>

        <div>
            <x-common.component-card title="Agregar boleto">
                <form method="POST" action="{{ route('ticket-types.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $event->id }}">

                    <x-form.input
                        name="name"
                        label="Nombre del boleto"
                        placeholder="Ej. Boleto individual"
                        required
                    />

                    <x-form.input
                        name="seats_per_ticket"
                        type="number"
                        min="1"
                        max="1000"
                        label="{{ __('Seats per ticket') }}"
                        :value="old('seats_per_ticket', 1)"
                        required
                    />

                    <x-form.input
                        name="early_price"
                        type="number"
                        step="0.01"
                        min="0"
                        label="Precio de preventa"
                        help="Déjalo vacío si el boleto siempre cuesta lo mismo."
                    />

                    <x-form.input
                        name="regular_price"
                        type="number"
                        step="0.01"
                        min="0"
                        label="Precio normal"
                        required
                    />

                    <div class="flex items-center gap-2">
                        <input
                            id="is_active"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            checked
                            class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700"
                        >
                        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-400">
                            Disponible para venta
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600"
                    >
                        Agregar boleto
                    </button>
                </form>
            </x-common.component-card>
        </div>
    </div>
@endsection
