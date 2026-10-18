@props([
    'id',
    'title' => '',
    'icon' => null,
    'size' => 'sm:max-w-xl',
    'formAction' => null,
    'formMethod' => 'POST',
    'formClass' => '',
    'formId' => null,
    'footer' => null,
])


<div id="{{ $id }}"
    class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none"
    role="dialog" tabindex="-1" aria-labelledby="{{ $id }}-label">
    <div
        class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all {{ $size }} sm:w-full m-3 sm:mx-auto">
        {{-- Outer Layer --}}
        <div
            class="bg-gray-50 border border-gray-200/50 rounded-[1.5rem] p-2.5 shadow-sm pointer-events-auto dark:bg-neutral-900 dark:border-neutral-800">
            {{-- Inner Layer --}}
            <div
                class="flex flex-col bg-white border border-gray-100 shadow-2xl shadow-gray-600/60 rounded-2xl overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">

                {{-- Header --}}
                <div
                    class="flex justify-between items-center py-5 px-6 border-b border-neutral-100 dark:border-neutral-700">
                    <div class="flex items-center gap-3">
                        @if ($icon)
                            <div class="p-2 bg-blue-50 rounded-xl dark:bg-blue-900/30">
                                {!! $icon !!}
                            </div>
                        @endif
                        <h2 id="{{ $id }}-label" class="font-semibold text-xl text-gray-800 dark:text-white">
                            {{ $title }}
                        </h2>
                    </div>
                    <button type="button"
                        class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600 transition-all cursor-pointer"
                        aria-label="Close" data-hs-overlay="#{{ $id }}">
                        <span class="sr-only">Close</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                @if ($formAction)
                    <form id="{{ $formId }}" action="{{ $formAction }}"
                        method="{{ strtoupper($formMethod) === 'GET' ? 'GET' : 'POST' }}"
                        {{ $attributes->merge(['class' => $formClass]) }} navigate-form>
                        @unblaze
                        @csrf
                        @if (!in_array(strtoupper($formMethod), ['GET', 'POST']))
                            @method($formMethod)
                        @endif
                        @endunblaze
                @endif


                {{-- Body --}}
                <div class="p-6 overflow-y-auto">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                @if ($footer)
                    <div
                        class="flex justify-end items-center gap-x-2 py-4 px-6 border-t border-neutral-100 dark:border-neutral-700">
                        {{ $footer }}
                    </div>
                @elseif($formAction)
                    <div
                        class="flex justify-end items-center gap-x-2 py-4 px-6 border-t border-neutral-100 dark:border-neutral-700">
                        <button type="button"
                            class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-bold rounded-xl border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 dark:focus:bg-neutral-800 transition-all cursor-pointer"
                            data-hs-overlay="#{{ $id }}">
                            Batal
                        </button>
                        <x-admin.button type="submit" class="font-bold py-2.5 px-6 rounded-xl">
                            Simpan
                        </x-admin.button>
                    </div>
                @endif

                @if ($formAction)
                    </form>
                @endif
            </div>
        </div>
    </div>

</div>
