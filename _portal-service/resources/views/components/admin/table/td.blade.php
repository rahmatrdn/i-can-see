@props(['innerClass' => 'px-6 py-3'])

<td {{ $attributes->merge(['class' => 'size-px whitespace-nowrap']) }}>
    <div class="{{ $innerClass }}">
        {{ $slot }}
    </div>
</td>
