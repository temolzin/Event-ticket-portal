@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle del evento" />

    <x-common.flash />

    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-common.component-card :title="$event->name">
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Lugar</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $event->venue }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Fecha</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">
                            {{ $event->starts_at->translatedFormat('d M Y, H:i') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Estado</dt>
                        <dd>
                            <x-ui.badge :color="$event->is_active ? 'success' : 'light'" size="sm">
                                {{ $event->is_active ? 'Activo' : 'Inactivo' }}
                            </x-ui.badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Boletos con precio de preventa</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $event->early_quantity }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Descripción</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">
                            {{ $event->description ?: 'Sin descripción' }}
                        </dd>
                    </div>
                </dl>
            </x-common.component-card>
        </div>

        <div class="space-y-5">
            <x-common.component-card title="Lugares">
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-gray-50 p-4 text-center dark:bg-white/5">
                        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $event->countSoldSeats() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Vendidos</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4 text-center dark:bg-white/5">
                        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $event->capacity }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Capacidad</p>
                    </div>
                    <div class="col-span-2 rounded-xl bg-brand-50 p-4 text-center dark:bg-brand-500/15">
                        <p class="text-2xl font-semibold text-brand-600 dark:text-brand-400">{{ $event->countAvailableSeats() }}</p>
                        <p class="text-xs text-brand-600 dark:text-brand-400">Disponibles</p>
                    </div>
                </div>
            </x-common.component-card>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('events.edit', $event) }}"
                    class="inline-flex flex-1 items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600"
                >
                    Editar
                </a>
                <form
                    method="POST"
                    action="{{ route('events.destroy', $event) }}"
                    class="flex-1"
                    x-data
                    @submit.prevent="confirmAction('¿Seguro que quieres eliminar este evento?').then(confirmed => { if (confirmed) $el.submit(); })"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        @disabled(! $event->canBeDeleted())
                        title="{{ $event->canBeDeleted() ? 'Eliminar evento' : $event->deleteBlockReason() }}"
                        class="inline-flex w-full items-center justify-center rounded-lg px-5 py-3 text-sm font-medium transition {{ $event->canBeDeleted() ? 'bg-error-50 text-error-500 hover:bg-error-100 dark:bg-error-500/15 dark:text-error-500' : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-600' }}"
                    >
                        Eliminar
                    </button>
                </form>
            </div>
        </div>

        <div class="min-w-0 lg:col-span-3">
            <x-common.component-card title="Tipos de boleto">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse($event->ticketTypes as $ticketType)
                        <div class="flex min-w-0 items-center justify-between gap-3 rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $ticketType->name }}</p>
                                <p class="break-words text-xs text-gray-500 dark:text-gray-400">
                                    Preventa
                                    {{ $ticketType->early_price ? '$' . number_format((float) $ticketType->early_price, 2) : '—' }}
                                    · Normal ${{ number_format((float) $ticketType->regular_price, 2) }}
                                </p>
                            </div>
                            <span class="shrink-0">
                                <x-ui.badge :color="$ticketType->is_active ? 'success' : 'light'" size="sm">
                                    {{ $ticketType->is_active ? 'Activo' : 'Inactivo' }}
                                </x-ui.badge>
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 sm:col-span-2 xl:col-span-3">
                            Este evento todavía no tiene boletos registrados.
                        </p>
                    @endforelse
                </div>

                <a
                    href="{{ route('events.prices', $event) }}"
                    class="mt-5 inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    Administrar precios
                </a>
            </x-common.component-card>
        </div>
    </div>
@endsection
