<div class="flex flex-col">
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div {{ $attributes->merge(['class' => 'bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden dark:bg-neutral-800 dark:border-neutral-700']) }}>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
