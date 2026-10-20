@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'step' => null,
    'min' => null,
    'help' => null,
])

@php
    $border = $errors->has($name)
        ? 'border-error-500 dark:border-error-500'
        : 'border-gray-300 dark:border-gray-700';
@endphp

<div>
    @if($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }}
            @if($required)
                <span class="text-error-500">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($step) step="{{ $step }}" @endif
        @if($min !== null) min="{{ $min }}" @endif
        {{ $attributes->merge(['class' => 'shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 ' . $border]) }}
    />

    @if($help)
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
    @enderror
</div>
