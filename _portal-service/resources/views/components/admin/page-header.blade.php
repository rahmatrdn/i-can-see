@props([
    'title' => '',
    'subtitle' => null,
    'backUrl' => null,
])

<div class="py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-y-3">
    <div class="flex items-center gap-x-3">
        @if ($backUrl)
            <x-admin.button href="{{ $backUrl }}" size="lg" color="outline-secondary">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
            </x-admin.button>
        @endif
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-neutral-200 mb-1">
                {{ $title }}
            </h1>
            @if ($subtitle)
                <p class="text-md text-gray-400 dark:text-neutral-400">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    </div>

    @if ($slot->isNotEmpty())
        <div class="flex items-center gap-x-2">
            {{ $slot }}
        </div>
    @endif
</div>
