@props(['fit' => false])

@php
    $heightClass = $fit ? 'h-auto' : 'h-full';
@endphp

<div
    {{ $attributes->merge(['class' => "border border-gray-200/50 rounded-3xl overflow-hidden dark:bg-gray-700 dark:border-gray-800 text-card-foreground shadow-sm {$heightClass} bg-sidebar p-2.5 bg-gray-50"]) }}>
    <div
        class="border border-gray-100 text-card-foreground shadow-2xl shadow-gray-500/20 {$heightClass} bg-sidebar p-2.5 rounded-2xl bg-white dark:bg-gray-800 dark:border-gray-800">
        <div class="p-2">
            {{ $slot }}
        </div>
    </div>
</div>
