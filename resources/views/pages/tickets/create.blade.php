@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Registrar venta" />

    <x-common.flash />

    @if($events->isEmpty())
        <x-ui.alert
            variant="warning"
            title="Sin eventos disponibles"
            message="No hay eventos activos con lugares disponibles para vender boletos."
        />
    @else
        <div
            x-data="saleForm({
                events: @js($events->map(fn ($event) => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'available' => $event->countAvailableSeats(),
                    'earlyPhase' => $event->isInEarlyPhase(),
                ])),
                typesUrl: @js(route('tickets.event-types', ['event' => '__ID__'])),
                cancelledMatchUrl: @js(route('tickets.cancelled-match')),
            })"
            class="grid grid-cols-1 gap-5 lg:grid-cols-3"
        >
            <div class="lg:col-span-2">
                <form
                    method="POST"
                    action="{{ route('tickets.store') }}"
                    enctype="multipart/form-data"
                    @submit.prevent="checkCancelledClient($event)"
                >
                    @csrf
                    <input type="hidden" name="confirm_cancelled_client" value="0">

                    <div class="space-y-6">
                        <x-common.component-card title="Evento y boleto">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="event_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Evento <span class="text-error-500">*</span>
                                    </label>
                                    <div class="relative z-20 bg-transparent">
                                        <select
                                            id="event_id"
                                            name="event_id"
                                            required
                                            x-model="eventId"
                                            @change="loadTypes()"
                                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border bg-transparent bg-none py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden ltr:pr-11 ltr:pl-4 rtl:pr-4 rtl:pl-11 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('event_id') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}"
                                        >
                                            <option value="">Selecciona el evento</option>
                                            <template x-for="event in events" :key="event.id">
                                                <option :value="event.id" x-text="event.name"></option>
                                            </template>
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 text-gray-500 ltr:right-4 rtl:left-4 dark:text-gray-400">
                                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                    @error('event_id')
                                        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="ticket_type_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Tipo de boleto <span class="text-error-500">*</span>
                                    </label>
                                    <div class="relative z-20 bg-transparent">
                                        <select
                                            id="ticket_type_id"
                                            name="ticket_type_id"
                                            required
                                            x-model="ticketTypeId"
                                            :disabled="! eventId"
                                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border bg-transparent bg-none py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden disabled:cursor-not-allowed disabled:bg-gray-100 ltr:pr-11 ltr:pl-4 rtl:pr-4 rtl:pl-11 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-white/5 {{ $errors->has('ticket_type_id') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}"
                                        >
                                            <option value="">Selecciona el tipo</option>
                                            <template x-for="type in types" :key="type.id">
                                                <option :value="type.id" x-text="type.label"></option>
                                            </template>
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 text-gray-500 ltr:right-4 rtl:left-4 dark:text-gray-400">
                                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                    @error('ticket_type_id')
                                        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div x-show="selectedEvent" x-cloak class="mt-5 rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                                <div class="flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Lugares disponibles</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="selectedEvent?.available ?? '—'"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Precio a cobrar</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="priceLabel"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Fase</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="selectedEvent?.earlyPhase ? 'Preventa' : 'Normal'"></p>
                                    </div>
                                </div>
                            </div>
                        </x-common.component-card>

                        <x-common.component-card title="Datos del cliente">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <x-form.input
                                    name="buyer_name"
                                    label="Nombre del cliente"
                                    placeholder="Nombre completo"
                                    required
                                />

                                <x-form.input
                                    name="buyer_email"
                                    type="email"
                                    label="Correo del cliente"
                                    placeholder="correo@ejemplo.com"
                                    help="Aquí se enviará el boleto digital."
                                    required
                                />

                                <x-form.input
                                    name="buyer_photo"
                                    type="file"
                                    accept="image/*"
                                    label="Foto del cliente"
                                    help="Se usa para el gafete. JPG, PNG o WEBP, hasta 5 MB."
                                    required
                                />

                                <x-form.input
                                    name="payment_receipt"
                                    type="file"
                                    accept="image/*,application/pdf"
                                    label="Comprobante de pago"
                                    help="Transferencia o depósito. JPG, PNG, WEBP o PDF, hasta 10 MB."
                                    required
                                />
                            </div>
                        </x-common.component-card>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                            @error('cancelled_client_confirmation')
                                <p class="w-full text-end text-sm text-error-500">{{ $message }}</p>
                            @enderror
                            <a
                                href="{{ route('tickets.index') }}"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                            >
                                Cancelar
                            </a>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600"
                            >
                                Registrar venta
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div>
                <x-common.component-card title="Cómo funciona">
                    <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <li>Registra los datos del cliente tal como vienen en su transferencia.</li>
                        <li>El sistema valida que el evento todavía tenga lugares antes de generar el boleto.</li>
                        <li>El precio se toma de la fase vigente: preventa mientras queden boletos de esa fase, después el precio normal.</li>
                        <li>La foto y el comprobante quedan en un almacenamiento privado, nunca expuestos al público.</li>
                    </ul>
                </x-common.component-card>
            </div>
        </div>

        @push('scripts')
            <script>
                function saleForm({ events, typesUrl, cancelledMatchUrl }) {
                    return {
                        events,
                        typesUrl,
                        cancelledMatchUrl,
                        eventId: '',
                        ticketTypeId: '',
                        types: [],
                        isCheckingCancelledClient: false,

                        get selectedEvent() {
                            return this.events.find((event) => String(event.id) === String(this.eventId)) ?? null;
                        },

                        get priceLabel() {
                            const type = this.types.find((item) => String(item.id) === String(this.ticketTypeId));

                            return type ? type.label.split(' · ').pop() : '—';
                        },

                        async loadTypes() {
                            this.ticketTypeId = '';
                            this.types = [];

                            if (!this.eventId) {
                                return;
                            }

                            const response = await fetch(this.typesUrl.replace('__ID__', this.eventId), {
                                headers: { Accept: 'application/json' },
                            });

                            if (!response.ok) {
                                return;
                            }

                            this.types = await response.json();
                        },

                        async checkCancelledClient(event) {
                            if (this.isCheckingCancelledClient) {
                                return;
                            }

                            this.isCheckingCancelledClient = true;

                            try {
                                const form = event.target;
                                const params = new URLSearchParams({
                                    buyer_name: form.elements.buyer_name.value,
                                    buyer_email: form.elements.buyer_email.value,
                                    event_id: form.elements.event_id.value,
                                });
                                const response = await fetch(`${this.cancelledMatchUrl}?${params}`, {
                                    headers: { Accept: 'application/json' },
                                });

                                if (!response.ok) {
                                    throw new Error('No se pudo verificar la lista de clientes cancelados.');
                                }

                                const result = await response.json();

                                if (result.exists) {
                                    const confirmed = await window.confirmAction(
                                        'Este cliente aparece en la lista de clientes cancelados. ¿Aun así deseas agregarlo?',
                                        {
                                            title: @js(__('Cancelled client')),
                                            confirmButtonText: @js(__('Yes, add')),
                                        }
                                    );

                                    if (! confirmed) {
                                        return;
                                    }
                                }

                                form.elements.confirm_cancelled_client.value = result.exists ? '1' : '0';
                                form.submit();
                            } catch (error) {
                                window.Swal.fire({
                                    icon: 'error',
                                    title: @js(__('Could not verify')),
                                    text: error.message,
                                });
                            } finally {
                                this.isCheckingCancelledClient = false;
                            }
                        },
                    };
                }
            </script>
        @endpush
    @endif
@endsection
