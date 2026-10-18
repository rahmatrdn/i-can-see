@props(['align' => 'start'])

<th scope="col" {{ $attributes->merge(['class' => 'px-6 py-3 text-' . $align]) }}>
    @if ($slot->isNotEmpty())
        <span class="font-semibold text-sm uppercase text-gray-500 dark:text-neutral-200 tracking-widest">
            {{ $slot }}
        </span>
    @endif
</th>
