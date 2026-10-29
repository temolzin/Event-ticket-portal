@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar cliente" />

    <x-common.flash />

    <form
        method="POST"
        action="{{ route('tickets.update', $ticket) }}"
        enctype="multipart/form-data"
        class="space-y-5"
    >
        @csrf
        @method('PUT')

        <x-common.component-card title="Datos del cliente">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="buyer_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nombre del cliente
                    </label>
                    <input
                        id="buyer_name"
                        name="buyer_name"
                        type="text"
                        value="{{ old('buyer_name', $ticket->buyer_name) }}"
                        required
                        maxlength="255"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    >
                    @error('buyer_name')
                        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="buyer_email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Correo del cliente
                    </label>
                    <input
                        id="buyer_email"
                        name="buyer_email"
                        type="email"
                        value="{{ old('buyer_email', $ticket->buyer_email) }}"
                        required
                        maxlength="255"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    >
                    @error('buyer_email')
                        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Actualizar archivos (opcional)">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="buyer_photo" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Foto del cliente
                    </label>
                    @if($ticket->buyer_photo_path)
                        <img
                            src="{{ route('tickets.photo', $ticket->encryptedDownloadKey()) }}"
                            alt="Foto actual de {{ $ticket->buyer_name }}"
                            class="mb-3 h-32 w-32 rounded-xl border border-gray-200 object-cover dark:border-gray-800"
                        >
                    @endif
                    <input
                        id="buyer_photo"
                        name="buyer_photo"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-gray-500 file:me-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-gray-700 dark:text-gray-400 dark:file:bg-white/5 dark:file:text-gray-300"
                    >
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Deja vacío para conservar la foto actual.</p>
                    @error('buyer_photo')
                        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_receipt" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Comprobante de pago
                    </label>
                    @if($ticket->payment_receipt_path)
                        <a
                            href="{{ route('tickets.receipt', $ticket->encryptedDownloadKey()) }}"
                            target="_blank"
                            rel="noopener"
                            class="mb-3 inline-flex text-sm text-brand-500 hover:underline"
                        >
                            Ver comprobante actual
                        </a>
                    @endif
                    <input
                        id="payment_receipt"
                        name="payment_receipt"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf"
                        class="block w-full text-sm text-gray-500 file:me-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-gray-700 dark:text-gray-400 dark:file:bg-white/5 dark:file:text-gray-300"
                    >
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Deja vacío para conservar el comprobante actual.</p>
                    @error('payment_receipt')
                        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-common.component-card>

        <div class="flex flex-wrap justify-end gap-3">
            <a
                href="{{ route('tickets.show', $ticket) }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
            >
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600"
            >
                Guardar cambios
            </button>
        </div>
    </form>
@endsection
