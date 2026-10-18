@extends('_admin._layout.app')

@section('title', 'Edit Menu Sidebar')

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
                        Edit Menu: {{ $data->label }}
                    </h2>
                </div>
            </div>

            <form class="p-6" navigate-form action="{{ route('admin.sidebar_menu.doUpdate', $data->id) }}" method="POST">
                @csrf

                <div class="space-y-4">
                    {{-- Label --}}
                    <div>
                        <label for="label" class="block text-sm font-medium mb-2 dark:text-white">Label Menu <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="label" name="label" value="{{ old('label', $data->label) }}"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 placeholder-neutral-300 dark:placeholder-neutral-500 @error('label') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                            placeholder="Contoh: Dashboard" required>
                        @error('label')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Group --}}
                    <div>
                        <label for="group" class="block text-sm font-medium mb-2 dark:text-white">Group <span
                                class="text-red-500">*</span></label>
                        <select id="group" name="group"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 @error('group') border-red-500 @enderror"
                            required>
                            <option value="">-- Pilih Group --</option>
                            @foreach ($groups as $g)
                                <option value="{{ $g->key }}" @selected(old('group', $data->group) == $g->key)>{{ $g->label }}</option>
                            @endforeach
                        </select>
                        @error('group')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Parent --}}
                    <div>
                        <label for="parent_id" class="block text-sm font-medium mb-2 dark:text-white">Parent Menu</label>
                        <select id="parent_id" name="parent_id"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 @error('parent_id') border-red-500 @enderror">
                            <option value="">-- Tidak ada (item root) --</option>
                            @foreach ($parentOptions as $opt)
                                @if ($opt->id != $data->id)
                                    <option value="{{ $opt->id }}" @selected(old('parent_id', $data->parent_id) == $opt->id)>
                                        [{{ ucfirst($opt->group) }}] {{ $opt->label }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1 dark:text-neutral-500">Isi jika menu ini adalah child dari accordion.</p>
                        @error('parent_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Route Name --}}
                    <div>
                        <label for="route_name" class="block text-sm font-medium mb-2 dark:text-white">Route Name</label>
                        <input type="text" id="route_name" name="route_name"
                            value="{{ old('route_name', $data->route_name) }}"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 font-mono placeholder-neutral-300 dark:placeholder-neutral-500 @error('route_name') border-red-500 @enderror"
                            placeholder="Contoh: admin.dashboard">
                        <p class="text-xs text-gray-500 mt-1 dark:text-neutral-500">Named route Laravel. Kosongkan jika item ini adalah accordion parent tanpa link.</p>
                        @error('route_name')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Icon --}}
                    <div>
                        <label for="icon" class="block text-sm font-medium mb-2 dark:text-white">Icon (blade include path)</label>
                        <input type="text" id="icon" name="icon" value="{{ old('icon', $data->icon) }}"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 font-mono placeholder-neutral-300 dark:placeholder-neutral-500 @error('icon') border-red-500 @enderror"
                            placeholder="Contoh: _admin._layout.icons.sidebar.dashboard">
                        @error('icon')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label for="sort_order" class="block text-sm font-medium mb-2 dark:text-white">Urutan Tampil</label>
                        <input type="number" id="sort_order" name="sort_order"
                            value="{{ old('sort_order', $data->sort_order) }}"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 @error('sort_order') border-red-500 @enderror"
                            min="0">
                        @error('sort_order')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Is Active --}}
                    <div>
                        <label for="is_active" class="block text-sm font-medium mb-2 dark:text-white">Status</label>
                        <select id="is_active" name="is_active"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                            <option value="1" @selected(old('is_active', $data->is_active) == 1)>Aktif</option>
                            <option value="0" @selected(old('is_active', $data->is_active) == 0)>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                        class="py-3 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 cursor-pointer">
                        Simpan Perubahan
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
