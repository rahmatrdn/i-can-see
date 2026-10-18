@props([
    'size' => 'md',
    'color' => 'primary',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'class' => '',
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'py-1.5 px-3 text-sm',
        'md' => 'py-2.5 px-4 text-sm',
        'lg' => 'py-3 px-5 text-base',
        'xl' => 'py-3.5 px-6 text-lg',
        default => 'py-2.5 px-4 text-sm',
    };

    $colorClasses = match ($color) {
        'primary'
            => 'bg-linear-to-b from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.2),0_1px_2px_rgba(0,0,0,0.1)] active:shadow-inner active:from-blue-700 active:to-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-blue-800',
        'secondary'
            => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:bg-gray-200 border-transparent dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-600 dark:focus:bg-neutral-600',
        'warning' => 'bg-yellow-500 text-white hover:bg-yellow-600 focus:bg-yellow-600 border-transparent',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:bg-red-700 border-transparent',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:bg-green-700 border-transparent',
        'outline-primary'
            => 'bg-white text-blue-600 hover:bg-blue-50 focus:bg-blue-50 border-blue-600/30 hover:border-blue-700 dark:bg-transparent dark:text-blue-500 dark:border-blue-500 dark:hover:bg-blue-500/10 dark:hover:border-blue-400',
        'outline-secondary'
            => 'bg-white text-gray-700 hover:bg-gray-50 focus:bg-gray-50 border-gray-200 hover:border-gray-300 dark:bg-transparent dark:text-neutral-300 dark:border-neutral-700 dark:hover:bg-neutral-800 shadow-xl shadow-gray-400/20',
        'outline-warning'
            => 'bg-white text-yellow-600 hover:bg-yellow-50 focus:bg-yellow-50 border-yellow-600/30 hover:border-yellow-700 dark:bg-transparent dark:text-yellow-500 dark:border-yellow-500 dark:hover:bg-yellow-500/10',
        'outline-danger'
            => 'bg-white text-red-600 hover:bg-red-50 focus:bg-red-50 border-red-600/30 hover:border-red-700 dark:bg-transparent dark:text-red-500 dark:border-red-500 dark:hover:bg-red-500/10',
        'outline-success'
            => 'bg-white text-green-600 hover:bg-green-50 focus:bg-green-50 border-green-600/30 hover:border-green-700 dark:bg-transparent dark:text-green-500 dark:border-green-500 dark:hover:bg-green-500/10',
        default => 'bg-blue-600 text-white hover:bg-blue-700 focus:bg-blue-700 border-transparent',
    };

    $baseClasses =
        'inline-flex items-center justify-center gap-x-2 font-medium rounded-xl border dark:border-1 transition-all duration-200 focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none';
    $classes = implode(' ', array_filter([$class, $baseClasses, $sizeClasses, $colorClasses]));

    $attributes = $attributes->merge([
        'type' => $href ? null : $type,
        'href' => $href,
        'disabled' => $disabled ? true : null,
        'class' => $classes . ' cursor-pointer',
    ]);
@endphp

@if ($href)
    <a navigate {{ $attributes }}>
        @if ($icon && $iconPosition === 'left')
            {!! $icon !!}
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'right')
            {!! $icon !!}
        @endif
    </a>
@else
    <button {{ $attributes }}>
        @if ($icon && $iconPosition === 'left')
            {!! $icon !!}
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'right')
            {!! $icon !!}
        @endif
    </button>
@endif
