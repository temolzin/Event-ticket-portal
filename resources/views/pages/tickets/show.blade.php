@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle del cliente" />

    <x-common.flash />

    <div class="mb-5 flex flex-wrap justify-end gap-3">
        @can('update', $ticket)
            <a
                href="{{ route('tickets.edit', $ticket) }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
            >
                Editar cliente
            </a>
        @endcan
        @can('cancel', $ticket)
            <form
                method="POST"
                action="{{ route('tickets.destroy', $ticket) }}"
                x-data
                @submit.prevent="confirmAction('¿Cancelar este boleto? El cliente aparecerá en Clientes cancelados.', { title: @js(__('Cancel client')), confirmButtonText: @js(__('Yes, cancel')), icon: 'warning' }).then(confirmed => { if (confirmed) $el.submit(); })"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-error-200 px-4 py-2.5 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/30 dark:text-error-400 dark:hover:bg-error-500/10"
                >
                    Cancelar cliente
                </button>
            </form>
        @endcan
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-common.component-card title="Datos del cliente">
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Nombre</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $ticket->buyer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Correo</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $ticket->buyer_email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Evento</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $ticket->event->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Tipo de boleto</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $ticket->ticketType->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Precio</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">${{ number_format((float) $ticket->price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Vendedor</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $ticket->seller->name }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Código</dt>
                        <dd class="font-mono text-sm text-gray-800 dark:text-white/90">{{ $ticket->code }}</dd>
                    </div>
                </dl>
            </x-common.component-card>

            <div class="mt-5">
                <x-common.component-card title="Archivos">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <p class="mb-2 text-xs uppercase text-gray-500 dark:text-gray-400">Foto del cliente</p>
                            @if($ticket->buyer_photo_path)
                                <img
                                    src="{{ route('tickets.photo', $ticket) }}"
                                    alt="Foto de {{ $ticket->buyer_name }}"
                                    class="h-40 w-full rounded-xl border border-gray-200 object-cover dark:border-gray-800"
                                >
                                <div class="mt-2 flex flex-wrap items-center gap-4">
                                    <a
                                        href="{{ route('tickets.photo', $ticket) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="text-xs text-brand-500 hover:underline"
                                    >
                                        Ver en tamaño completo
                                    </a>
                                    <a
                                        href="{{ route('tickets.photo-download', $ticket) }}"
                                        class="text-xs text-brand-500 hover:underline"
                                    >
                                        Descargar foto
                                    </a>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sin foto registrada.</p>
                            @endif
                        </div>

                        <div>
                            <p class="mb-2 text-xs uppercase text-gray-500 dark:text-gray-400">Comprobante de pago</p>
                            @if($ticket->payment_receipt_path)
                                <a
                                    href="{{ route('tickets.receipt', $ticket) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
                                >
                                    Ver comprobante
                                </a>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sin comprobante registrado.</p>
                            @endif
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                        Los archivos se sirven desde una pantalla protegida y nunca quedan disponibles al público.
                        Solo el vendedor que registró la venta y los administradores pueden verlos.
                    </p>
                </x-common.component-card>
            </div>
        </div>

        <div class="space-y-5">
            <x-common.component-card title="Boleto">
                <div class="flex flex-col items-center">
                    <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800">
                        {!! $ticket->qrCode() !!}
                    </div>
                    <p class="mt-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $ticket->code }}</p>
                </div>

                <div class="mt-5 space-y-2">
                    <a
                        href="{{ route('tickets.download', ['ticket' => $ticket->encryptedDownloadKey()]) }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                    >
                        Descargar para imprimir
                    </a>

                    <form method="POST" action="{{ route('tickets.send', $ticket) }}">
                        @csrf
                        <button
                            type="submit"
                            @disabled($ticket->isCancelled() || $ticket->wasUsed())
                            class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ $ticket->wasSent() ? 'Enviar de nuevo' : 'Enviar al cliente' }}
                        </button>
                    </form>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Estado">
                <div class="space-y-4">
                    <x-ui.badge
                        :color="match ($ticket->status->value) {
                            'cancelled' => 'error',
                            'used' => 'warning',
                            'sent' => 'info',
                            default => 'success',
                        }"
                        size="sm"
                    >
                        {{ $ticket->status->describe() }}
                    </x-ui.badge>

                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Registrado</dt>
                            <dd class="text-sm text-gray-800 dark:text-white/90">
                                {{ $ticket->created_at->translatedFormat('d M Y, H:i') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Enviado</dt>
                            <dd class="text-sm text-gray-800 dark:text-white/90">
                                {{ $ticket->sent_at?->translatedFormat('d M Y, H:i') ?? 'Todavía no enviado' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Check-in</dt>
                            <dd class="text-sm text-gray-800 dark:text-white/90">
                                {{ $ticket->checked_in_at?->translatedFormat('d M Y, H:i') ?? 'Sin registrar' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </x-common.component-card>

            <a
                href="{{ route('tickets.index') }}"
                class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
            >
                Volver a la lista
            </a>
        </div>
    </div>
@endsection
