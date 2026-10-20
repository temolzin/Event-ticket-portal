@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Eventos') }}" />

    <x-common.flash />

    <div class="mb-5 flex justify-end">
        <a
            href="{{ route('events.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 4.16669V15.8334M4.16669 10H15.8334" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            {{ __('Nuevo evento') }}
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[880px]">
                <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Evento') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Fecha') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Lugar') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Lugares') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Estado') }}</th>
                        <th class="px-5 py-3 text-end text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($events as $event)
                        <tr>
                            <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $event->name }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $event->starts_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $event->venue }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $event->sold_seats_count }} / {{ $event->capacity }}
                                <span class="block text-xs text-gray-400 dark:text-gray-500">
                                    {{ $event->countAvailableSeats() }} {{ __('disponibles') }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <x-ui.badge :color="$event->is_active ? 'success' : 'light'" size="sm">
                                    {{ $event->is_active ? __('Activo') : __('Inactivo') }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('events.show', $event) }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                                    >
                                        {{ __('Ver') }}
                                    </a>
                                    <a
                                        href="{{ route('events.edit', $event) }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                                    >
                                        {{ __('Editar') }}
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('events.destroy', $event) }}"
                                        x-data
                                        @submit.prevent="if (confirm('{{ __('¿Seguro que quieres eliminar este evento?') }}')) $el.submit()"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            @disabled(! $event->canBeDeleted())
                                            title="{{ $event->canBeDeleted() ? __('Eliminar evento') : __('Se desbloquea un día después del evento') }}"
                                            class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-xs font-medium transition {{ $event->canBeDeleted() ? 'bg-error-50 text-error-500 hover:bg-error-100 dark:bg-error-500/15 dark:text-error-500' : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-600' }}"
                                        >
                                            {{ __('Eliminar') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Todavía no hay eventos registrados.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $events->links() }}
    </div>
@endsection
