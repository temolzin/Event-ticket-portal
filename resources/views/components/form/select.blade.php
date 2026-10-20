@props([
    'name',
    'label' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'required' => false,
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

    <div class="relative z-20 bg-transparent">
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            @if($required) required @endif
            {{ $attributes->merge(['class' => 'shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border bg-transparent bg-none py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden ltr:pr-11 ltr:pl-4 rtl:pr-4 rtl:pl-11 dark:bg-gray-900 dark:text-white/90 ' . $border]) }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif

            @foreach($options as $optionValue => $optionLabel)
                <option
                    value="{{ $optionValue }}"
                    @selected((string) old($name, $value) === (string) $optionValue)
                    class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
                >
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>

        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 text-gray-500 ltr:right-4 rtl:left-4 dark:text-gray-400">
            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
    </div>

    @if($help)
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
    @enderror
</div>
