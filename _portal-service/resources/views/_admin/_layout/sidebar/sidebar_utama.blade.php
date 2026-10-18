@php
    use App\Constants\UserConst;
@endphp

@if (!empty($sidebarMenus['utama']))
    @foreach ($sidebarMenus['utama'] as $menu)
        @include('_admin._layout.sidebar._menu_item', ['menu' => $menu])
    @endforeach
@else
    {{-- Fallback: no menus configured for this role --}}
    <li class="px-3 py-4">
        <p class="text-xs text-gray-400 dark:text-neutral-500 text-center">Tidak ada menu tersedia.</p>
    </li>
@endif