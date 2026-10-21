@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle del boleto" />

    <x-common.flash />

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
        </div>

        <div class="space-y-5">
            <x-common.component-card title="Estado">
                <div class="space-y-4">
                    <x-ui.badge
                        :color="$ticket->status->value === 'cancelled' ? 'error' : 'success'"
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

            <x-common.component-card title="Archivos">
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li>Foto del cliente: {{ $ticket->buyer_photo_path ? 'Guardada' : 'Sin foto' }}</li>
                    <li>Comprobante de pago: {{ $ticket->payment_receipt_path ? 'Guardado' : 'Sin comprobante' }}</li>
                </ul>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Los archivos se consultan desde una pantalla protegida. Solo el vendedor que registró la venta y los administradores pueden verlos.
                </p>
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
