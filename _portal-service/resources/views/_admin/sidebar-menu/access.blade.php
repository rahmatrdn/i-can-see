@extends('_admin._layout.app')

@section('title', 'Kelola Akses Menu')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
            class="bg-white overflow-hidden shadow-lg rounded-2xl dark:bg-neutral-800 border-2 border-gray-100 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center">
                <a href="{{ route('admin.sidebar_menu.index') }}"
                    class="py-3 px-3 inline-flex items-center gap-x-2 text-xl rounded-xl border border-gray-200 bg-white text-gray-800 shadow-md hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 cursor-pointer">
                    <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7" />
                        <path d="M19 12H5" />
                    </svg>
                </a>
                <div class="ms-3">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                        Hak Akses: {{ $data->label }}
                    </h2>
                    @php
                        $groupColors = [
                            'utama' => 'bg-blue-100 text-blue-700',
                        ];
                        $colorClass = $groupColors[$data->group] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold mt-1 {{ $colorClass }}">
                        {{ ucfirst($data->group) }}
                    </span>
                </div>
            </div>

            <form class="p-6" navigate-form action="{{ route('admin.sidebar_menu.doAccess', $data->id) }}" method="POST">
                @csrf

                <p class="text-sm text-gray-500 dark:text-neutral-400 mb-5">
                    Pilih role pengguna yang dapat melihat menu ini di sidebar.
                </p>

                <div class="space-y-3">
                    @foreach ($accessTypes as $typeValue => $typeLabel)
                        <label
                            class="flex items-center gap-x-3 p-3 rounded-xl border cursor-pointer transition-all
                            {{ in_array($typeValue, $accesses) ? 'border-blue-300 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 dark:border-neutral-700 dark:bg-neutral-900 dark:hover:border-neutral-600' }}">
                            <input type="checkbox" name="access_types[]" value="{{ $typeValue }}"
                                class="shrink-0 size-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-600 cursor-pointer"
                                {{ in_array($typeValue, $accesses) ? 'checked' : '' }}>
                            <div>
                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                    {{ $typeLabel }}
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-neutral-400">
                                    Access type {{ $typeValue }}
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                        class="py-3 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 cursor-pointer">
                        Simpan Hak Akses
                    </button>
                    <a navigate href="{{ route('admin.sidebar_menu.index') }}"
                        class="py-3 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
