@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Check-in" />

    <x-common.flash />

    <div
        class="mx-auto max-w-2xl"
        data-check-in
        data-store-url="{{ route('check-in.store') }}"
        data-invalid-qr-title="{{ __('Invalid or unknown QR code') }}"
        data-invalid-qr-description="{{ __('The scanned QR code does not match a registered ticket. Check the QR code or enter the ticket code manually.') }}"
        data-validation-error-title="{{ __('Unable to validate QR code') }}"
        data-validation-error-description="{{ __('An unexpected error occurred while validating this code. Please try again.') }}"
        data-try-again-label="{{ __('Scan another ticket') }}"
    >
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Escanear boleto</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Apunta la cámara al código QR que recibió el cliente.
                    </p>
                </div>

                <x-ui.badge color="light" size="sm">{{ auth()->user()->name }}</x-ui.badge>
            </div>

            <div
                id="check-in-reader"
                class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-gray-950 [&_canvas]:block [&_canvas]:w-full [&_video]:block [&_video]:w-full dark:border-gray-800"
            ></div>

            <p id="check-in-camera-message" class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Encendiendo la cámara...
            </p>

            <button
                type="button"
                id="check-in-camera-toggle"
                class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
            >
                Encender cámara
            </button>
        </div>

        <div class="mt-5 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">¿La cámara no funciona?</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Escribe el código del boleto a mano para validarlo.
            </p>

            <form
                method="POST"
                action="{{ route('check-in.store') }}"
                id="check-in-form"
                class="mt-4"
            >
                @csrf

                <x-form.input
                    name="code"
                    label="Código del boleto"
                    placeholder="01M5FVPM2P3NXAX7RS5V83WT8V"
                    help="El código aparece debajo del código QR en el boleto del cliente."
                    required
                />

                <button
                    type="submit"
                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    Validar código
                </button>
            </form>
        </div>

        <div id="check-in-result" class="mt-5"></div>
    </div>
@endsection
