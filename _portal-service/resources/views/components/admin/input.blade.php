@props([
    'label',
    'name',
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'autocomplete' => null,
    'error' => null,
    'size' => 'md',
])

@php
    $inputId = $id ?? $name;
    $inputValue = $value ?? old($name);

    $sizeClasses = match ($size) {
        'sm' => 'py-1.5 px-3 text-sm',
        'lg' => 'p-3.5 sm:p-5 sm:text-sm',
        default => 'py-2.5 sm:py-3 px-4 sm:text-sm',
    };

    $baseInputClasses = "{$sizeClasses} block w-full border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 placeholder-gray-400";
    $disabledClass = $disabled ? 'opacity-50 cursor-not-allowed' : '';
    $readonlyClass = $readonly ? 'bg-gray-50 dark:bg-neutral-800/50' : '';
    $inputClasses = implode(
        ' ',
        array_filter([$baseInputClasses, $disabledClass, $readonlyClass, $attributes->get('class')]),
    );

    $inputAttributes = $attributes->merge([
        'id' => $inputId,
        'type' => $type,
        'name' => $name,
        'value' => $inputValue,
        'placeholder' => $placeholder,
        'required' => $required ? true : null,
        'disabled' => $disabled ? true : null,
        'readonly' => $readonly ? true : null,
        'autocomplete' => $autocomplete,
        'class' => $inputClasses,
    ]);
@endphp

<div class="space-y-2">
    @if ($label)
        <label for="{{ $inputId }}" class="text-sm text-gray-600 dark:text-neutral-200 pb-3">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input {{ $inputAttributes }}>

    @if ($error)
        <p class="text-xs text-red-600 mt-1">{{ $error }}</p>
    @endif
</div>
