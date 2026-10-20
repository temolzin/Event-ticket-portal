@php
    $event = $event ?? null;
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
