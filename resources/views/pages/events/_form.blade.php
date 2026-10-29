@php
    $event = $event ?? null;
    $ticketRows = $ticketRows ?? [['name' => '', 'seats_per_ticket' => 1, 'early_price' => '', 'regular_price' => '']];
@endphp

<div class="space-y-6">
    <x-common.component-card title="Datos del evento">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-form.input
                name="name"
                label="Nombre del evento"
                :value="$event?->name"
                required
            />

            <x-form.input
                name="venue"
                label="Lugar"
                :value="$event?->venue"
                required
            />

            <x-form.input
                name="starts_at"
                type="datetime-local"
                label="Fecha y hora"
                :value="$event?->starts_at?->format('Y-m-d\TH:i')"
                required
            />

            <x-form.input
                name="capacity"
                type="number"
                min="1"
                label="Capacidad total"
                :value="$event?->capacity"
                required
            />

            <x-form.input
                name="early_quantity"
                type="number"
                min="0"
                label="Boletos con precio de preventa"
                :value="$event?->early_quantity ?? 0"
                help="Después de esta cantidad se cobra el precio normal."
                required
            />

            <x-form.select
                name="is_active"
                label="Estado"
                :value="old('is_active', $event?->is_active ?? true) ? '1' : '0'"
                :options="['1' => 'Activo', '0' => 'Inactivo']"
                required
            />
        </div>

        <div>
            <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Descripción
            </label>
            <textarea
                id="description"
                name="description"
                rows="3"
                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            >{{ old('description', $event?->description) }}</textarea>
            @error('description')
                <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>
    </x-common.component-card>

    <x-common.component-card title="Precios de los boletos">
        @if($event)
            <div class="mb-5 space-y-3">
                @forelse($event->ticketTypes as $ticketType)
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3 last:border-0 last:pb-0 dark:border-gray-800">
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $ticketType->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Preventa
                                {{ $ticketType->early_price ? '$' . number_format((float) $ticketType->early_price, 2) : '—' }}
                                · Normal ${{ number_format((float) $ticketType->regular_price, 2) }}
                            </p>
                        </div>
                        <x-ui.badge :color="$ticketType->is_active ? 'success' : 'light'" size="sm">
                            {{ $ticketType->is_active ? 'Activo' : 'Inactivo' }}
                        </x-ui.badge>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Este evento todavía no tiene boletos registrados.
                    </p>
                @endforelse
            </div>
        @endif

        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Agrega aquí los boletos nuevos. Para editar o eliminar los existentes usa
            @if($event)
                <a href="{{ route('events.prices', $event) }}" class="text-brand-500 hover:underline">la pantalla de precios</a>.
            @else
                la pantalla de precios del evento.
            @endif
        </p>

        <div
            x-data="{
                rows: @js($ticketRows),
                addRow() {
                    this.rows.push({ name: '', seats_per_ticket: 1, early_price: '', regular_price: '' });
                },
                removeRow(index) {
                    this.rows.splice(index, 1);
                },
            }"
            class="space-y-4"
        >
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-4 sm:grid-cols-12 dark:border-gray-800">
                    <div class="sm:col-span-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nombre del boleto</label>
                        <input
                            type="text"
                            :name="`ticket_types[${index}][name]`"
                            x-model="row.name"
                            required
                            placeholder="Ej. Boleto individual"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        >
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Preventa</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            :name="`ticket_types[${index}][early_price]`"
                            x-model="row.early_price"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        >
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Normal</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            :name="`ticket_types[${index}][regular_price]`"
                            x-model="row.regular_price"
                            required
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        >
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Seats per ticket') }}</label>
                        <input
                            type="number"
                            min="1"
                            max="1000"
                            :name="`ticket_types[${index}][seats_per_ticket]`"
                            x-model="row.seats_per_ticket"
                            required
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90"
                        >
                    </div>
                    <div class="flex items-end sm:col-span-2">
                        <button
                            type="button"
                            @click="removeRow(index)"
                            title="Quitar"
                            class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-error-50 text-error-500 transition hover:bg-error-100 dark:bg-error-500/15"
                        >
                            &times;
                        </button>
                    </div>
                </div>
            </template>

            <button
                type="button"
                @click="addRow()"
                class="inline-flex items-center gap-2 rounded-lg border border-dashed border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5"
            >
                + Agregar boleto
            </button>

            @error('ticket_types')
                <p class="text-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>
    </x-common.component-card>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <a
            href="{{ route('events.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
        >
            Cancelar
        </a>
        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            {{ $submitLabel }}
        </button>
    </div>
</div>
