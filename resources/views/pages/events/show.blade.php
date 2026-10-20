@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Detalle del evento') }}" />

    <x-common.flash />

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-common.component-card :title="$event->name">
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Lugar') }}</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $event->venue }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Fecha') }}</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">
                            {{ $event->starts_at->translatedFormat('d M Y, H:i') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Estado') }}</dt>
                        <dd>
                            <x-ui.badge :color="$event->is_active ? 'success' : 'light'" size="sm">
                                {{ $event->is_active ? __('Activo') : __('Inactivo') }}
                            </x-ui.badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Boletos con precio de preventa') }}</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $event->early_quantity }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Descripción') }}</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">
                            {{ $event->description ?: __('Sin descripción') }}
                        </dd>
                    </div>
                </dl>
            </x-common.component-card>
        </div>

        <div class="space-y-5">
            <x-common.component-card :title="__('Lugares')">
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-gray-50 p-4 text-center dark:bg-white/5">
                        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $event->sold_seats_count }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Vendidos') }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4 text-center dark:bg-white/5">
                        <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $event->capacity }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Capacidad') }}</p>
                    </div>
                    <div class="col-span-2 rounded-xl bg-brand-50 p-4 text-center dark:bg-brand-500/15">
                        <p class="text-2xl font-semibold text-brand-600 dark:text-brand-400">{{ $event->countAvailableSeats() }}</p>
                        <p class="text-xs text-brand-600 dark:text-brand-400">{{ __('Disponibles') }}</p>
                    </div>
                </div>
            </x-common.component-card>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('events.edit', $event) }}"
                    class="inline-flex flex-1 items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600"
                >
                    {{ __('Editar') }}
                </a>
                <form
                    method="POST"
                    action="{{ route('events.destroy', $event) }}"
                    class="flex-1"
                    x-data
                    @submit.prevent="if (confirm('{{ __('¿Seguro que quieres eliminar este evento?') }}')) $el.submit()"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        @disabled(! $event->canBeDeleted())
                        title="{{ $event->canBeDeleted() ? __('Eliminar evento') : __('Se desbloquea un día después del evento') }}"
                        class="inline-flex w-full items-center justify-center rounded-lg px-5 py-3 text-sm font-medium transition {{ $event->canBeDeleted() ? 'bg-error-50 text-error-500 hover:bg-error-100 dark:bg-error-500/15 dark:text-error-500' : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-600' }}"
                    >
                        {{ __('Eliminar') }}
                    </button>
                </form>
            </div>

            <x-common.component-card :title="__('Tipos de boleto')">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Los precios del evento se administran desde la pantalla de precios.') }}
                </p>
            </x-common.component-card>
        </div>
    </div>
@endsection
