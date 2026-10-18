@props([
    'label',
    'name',
    'id' => null,
    'options' => [],
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'value' => null,
    'size' => 'md',
])
@php
    $selectId = $id ?? $name;

    $sizeClasses = match ($size) {
        'sm' => 'py-1.5 px-3 text-sm',
        'lg' => 'p-3.5 sm:p-5 pe-9 sm:pe-9 sm:text-sm',
        default => 'py-2.5 sm:py-3 px-4 pe-9 sm:pe-9 sm:text-sm',
    };

    $baseClasses = "{$sizeClasses} block w-full border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400";
    $disabledClass = $disabled ? 'opacity-50 cursor-not-allowed' : '';
    $customClass = $attributes->get('class');
    $selectClass = trim(implode(' ', array_filter([$baseClasses, $disabledClass, $customClass])));
@endphp
<div class="space-y-2">
    @if ($label)
        <label for="{{ $selectId }}" class="text-sm text-gray-600 dark:text-neutral-200">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    <select id="{{ $selectId }}" name="{{ $name }}" class="{{ $selectClass }}"
        {{ $disabled ? 'disabled' : '' }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $text)
            <option value="{{ $optionValue }}" {{ (old($name) ?? $value) == $optionValue ? 'selected' : '' }}>
                {{ $text }}</option>
        @endforeach
    </select>
    @if ($error)
        <p class="text-xs text-red-600 mt-1">{{ $error }}</p>
    @endif
</div>
