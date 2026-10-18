@extends('_admin._layout.app')

@section('title', 'Hak Akses per Role')

@section('content')
    <x-admin.page-header :title="'Hak Akses Menu: ' . $roleName" subtitle="Centang menu yang aktif untuk role ini">
        <a navigate href="{{ route('admin.sidebar_menu.index') }}"
            class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7" />
                <path d="M19 12H5" />
            </svg>
            Kembali
        </a>
    </x-admin.page-header>

    {{-- Role tabs --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($accessTypes as $typeValue => $typeLabel)
            <a navigate href="{{ route('admin.sidebar_menu.role_access', $typeValue) }}"
                class="py-2 px-4 inline-flex items-center gap-x-1.5 text-sm font-semibold rounded-lg border transition-all
                {{ $typeValue === $accessType
                    ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                {{ $typeLabel }}
            </a>
        @endforeach
    </div>

    <form navigate-form action="{{ route('admin.sidebar_menu.doRoleAccess', $accessType) }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @php
                $colorMap = [
                    'blue' => [
                        'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        'header' => 'border-blue-200 dark:border-blue-800',
                        'check' => 'text-blue-600 focus:ring-blue-500',
                    ],
                    'purple' => [
                        'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                        'header' => 'border-purple-200 dark:border-purple-800',
                        'check' => 'text-purple-600 focus:ring-purple-500',
                    ],
                    'emerald' => [
                        'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'header' => 'border-emerald-200 dark:border-emerald-800',
                        'check' => 'text-emerald-600 focus:ring-emerald-500',
                    ],
                    'orange' => [
                        'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                        'header' => 'border-orange-200 dark:border-orange-800',
                        'check' => 'text-orange-600 focus:ring-orange-500',
                    ],
                ];
                $groupsKeyed = collect($groups)->keyBy('key');
            @endphp

            @foreach ($menusByGroup as $groupKey => $menus)
                @php
                    $groupObj = $groupsKeyed[$groupKey] ?? null;
                    $groupLabel = $groupObj ? $groupObj->label : ucfirst($groupKey);
                    $groupColor = $groupObj ? $groupObj->color : 'blue';
                    $color = $colorMap[$groupColor] ?? $colorMap['blue'];
                    $groupId = 'group-' . $groupKey;
                @endphp

                <div
                    class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:bg-neutral-800 dark:border-neutral-700">
                    {{-- Group header --}}
                    <div
                        class="px-5 py-3 border-b {{ $color['header'] }} bg-gray-50 dark:bg-neutral-900 flex items-center justify-between">
                        <div class="flex items-center gap-x-2">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $color['badge'] }}">
                                {{ $groupLabel }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-neutral-400">
                                {{ count($menus) }} menu
                            </span>
                        </div>
                        {{-- Select all toggle --}}
                        <label class="flex items-center gap-x-2 cursor-pointer text-xs text-gray-500 dark:text-neutral-400">
                            <input type="checkbox" class="group-select-all shrink-0 size-4 rounded border-gray-300 {{ $color['check'] }} dark:bg-neutral-800 dark:border-neutral-600 cursor-pointer"
                                data-group="{{ $groupId }}">
                            <span>Pilih semua</span>
                        </label>
                    </div>

                    <div class="p-4 space-y-2" id="{{ $groupId }}">
                        @forelse ($menus as $menu)
                            {{-- Parent menu --}}
                            <div class="rounded-xl border {{ $menu->is_enabled ? 'border-blue-200 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-900/10' : 'border-gray-100 bg-white dark:border-neutral-700 dark:bg-neutral-900' }}">
                                <label
                                    class="flex items-center gap-x-3 px-4 py-2.5 cursor-pointer rounded-xl">
                                    <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}"
                                        class="menu-checkbox shrink-0 size-4 rounded border-gray-300 {{ $color['check'] }} dark:bg-neutral-800 dark:border-neutral-600 cursor-pointer"
                                        data-group="{{ $groupId }}"
                                        {{ $menu->is_enabled ? 'checked' : '' }}>
                                    <div class="flex items-center gap-x-2 flex-1 min-w-0">
                                        @if ($menu->icon)
                                            <span class="shrink-0 inline-flex items-center justify-center w-4 h-4 text-gray-400 [&>svg]:w-4 [&>svg]:h-4">
                                                @include($menu->icon)
                                            </span>
                                        @else
                                            <span class="shrink-0 w-4 h-4"></span>
                                        @endif
                                        <span class="text-sm font-semibold text-gray-800 dark:text-neutral-200 truncate">
                                            {{ $menu->label }}
                                        </span>
                                        @if (! $menu->route_name)
                                            <span
                                                class="inline-flex shrink-0 items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-neutral-400">
                                                accordion
                                            </span>
                                        @endif
                                    </div>
                                    @if ($menu->route_name)
                                        <span
                                            class="text-xs font-mono text-gray-400 dark:text-neutral-500 hidden sm:block">
                                            {{ $menu->route_name }}
                                        </span>
                                    @endif
                                </label>

                                {{-- Children --}}
                                @if (! empty($menu->children) && count($menu->children) > 0)
                                    <div class="pb-2 px-4 space-y-1.5 border-t border-dashed border-gray-100 dark:border-neutral-700 pt-2">
                                        @foreach ($menu->children as $child)
                                            <label
                                                class="flex items-center gap-x-3 px-3 py-2 cursor-pointer rounded-lg transition-all
                                                {{ $child->is_enabled ? 'bg-blue-50 dark:bg-blue-900/10' : 'hover:bg-gray-50 dark:hover:bg-neutral-800' }}">
                                                <div class="size-4 shrink-0 flex items-center justify-center">
                                                    <span class="size-1.5 rounded-full bg-gray-300 dark:bg-neutral-500"></span>
                                                </div>
                                                <input type="checkbox" name="menu_ids[]" value="{{ $child->id }}"
                                                    class="menu-checkbox shrink-0 size-4 rounded border-gray-300 {{ $color['check'] }} dark:bg-neutral-800 dark:border-neutral-600 cursor-pointer"
                                                    data-group="{{ $groupId }}"
                                                    {{ $child->is_enabled ? 'checked' : '' }}>
                                                <span class="text-sm text-gray-700 dark:text-neutral-300 flex-1">
                                                    {{ $child->label }}
                                                </span>
                                                @if ($child->route_name)
                                                    <span
                                                        class="text-xs font-mono text-gray-400 dark:text-neutral-500 hidden sm:block">
                                                        {{ $child->route_name }}
                                                    </span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 dark:text-neutral-500 py-4 text-center">
                                Belum ada menu di group ini.
                            </p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit"
                class="py-3 px-8 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 cursor-pointer">
                Simpan Hak Akses
            </button>
            <a navigate href="{{ route('admin.sidebar_menu.index') }}"
                class="py-3 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700">
                Batal
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        // Select all / deselect all per group
        document.querySelectorAll('.group-select-all').forEach(function(selectAll) {
            const groupId = selectAll.dataset.group;

            // Set initial state based on whether all checked
            const checkboxes = document.querySelectorAll(`.menu-checkbox[data-group="${groupId}"]`);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const noneChecked = Array.from(checkboxes).every(cb => !cb.checked);
            selectAll.checked = allChecked;
            selectAll.indeterminate = !allChecked && !noneChecked;

            selectAll.addEventListener('change', function() {
                document.querySelectorAll(`.menu-checkbox[data-group="${groupId}"]`).forEach(function(cb) {
                    cb.checked = selectAll.checked;
                    updateRowHighlight(cb);
                });
            });
        });

        // Update "select all" state when individual checkbox changes
        document.querySelectorAll('.menu-checkbox').forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateRowHighlight(cb);
                syncSelectAll(cb.dataset.group);
            });
        });

        function updateRowHighlight(cb) {
            const label = cb.closest('label');
            if (!label) return;
            if (cb.checked) {
                label.classList.add('bg-blue-50', 'dark:bg-blue-900/10');
                label.classList.remove('hover:bg-gray-50', 'dark:hover:bg-neutral-800');
            } else {
                label.classList.remove('bg-blue-50', 'dark:bg-blue-900/10');
                label.classList.add('hover:bg-gray-50', 'dark:hover:bg-neutral-800');
            }
        }

        function syncSelectAll(groupId) {
            const selectAll = document.querySelector(`.group-select-all[data-group="${groupId}"]`);
            if (!selectAll) return;
            const checkboxes = document.querySelectorAll(`.menu-checkbox[data-group="${groupId}"]`);
            const checked = Array.from(checkboxes).filter(cb => cb.checked).length;
            selectAll.checked = checked === checkboxes.length;
            selectAll.indeterminate = checked > 0 && checked < checkboxes.length;
        }
    </script>
@endpush
