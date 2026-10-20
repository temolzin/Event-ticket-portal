@props([
    'title',
    'description',
])

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
    <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white/90">
        {{ $title }}
    </h3>

    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ $description }}
    </p>
</div>
