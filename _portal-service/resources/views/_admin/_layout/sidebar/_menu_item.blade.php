{{--
    Partial: sidebar menu item
    Variables:
    - $menu: object with id, label, route_name, icon, children (array)
    - $routeParams: optional array of route parameters
--}}
@php
    $routeParams = $routeParams ?? [];
    $hasChildren = !empty($menu->children) && count($menu->children) > 0;
    $accordionId = 'db-menu-accordion-' . $menu->id;

    // Check if a route name is active (exact, resource sub-routes, or named sub-routes)
    $checkRouteActive = function (string $routeName, array $siblingRoutes = []): bool {
        try {
            if (request()->routeIs($routeName)) {
                return true;
            }

            // .index routes cover sibling pages (detail, add, …) under the same resource,
            // unless a more specific sibling route matches the current page.
            if (str_ends_with($routeName, '.index')) {
                $resourcePrefix = substr($routeName, 0, -strlen('.index'));
                if (request()->routeIs($resourcePrefix . '.*')) {
                    foreach ($siblingRoutes as $siblingRoute) {
                        if ($siblingRoute !== $routeName && $siblingRoute !== '' && request()->routeIs($siblingRoute . '*')) {
                            return false;
                        }
                    }

                    return true;
                }

                return false;
            }

            // Non-index routes with a meaningful prefix (3+ segments)
            if (substr_count($routeName, '.') >= 2) {
                return request()->routeIs($routeName . '.*') || request()->routeIs($routeName . '*');
            }
        } catch (\Exception $e) {
            // ignore
        }

        return false;
    };

    $siblingRoutes = $hasChildren
        ? collect($menu->children)->pluck('route_name')->filter()->values()->all()
        : [];

    // Determine if this menu item (or its children) is active
    $isActive = false;
    if (!$hasChildren && $menu->route_name) {
        $isActive = $checkRouteActive($menu->route_name);
    } elseif ($hasChildren) {
        foreach ($menu->children as $child) {
            if ($child->route_name && $checkRouteActive($child->route_name, $siblingRoutes)) {
                $isActive = true;
                break;
            }
        }
    }

    $activeClass = 'bg-linear-to-r from-blue-50 to-indigo-50 text-blue-700 border-blue-200 dark:from-blue-900/20 dark:to-indigo-900/20 dark:text-blue-400 dark:border-blue-800';
    $inactiveClass = 'text-gray-700 hover:bg-linear-to-r hover:from-gray-100 hover:to-gray-50 border-transparent hover:border-gray-200 dark:text-neutral-300 dark:hover:from-neutral-700/50 dark:hover:to-neutral-800/50 dark:hover:border-neutral-600';
@endphp

@if (!$hasChildren)
    {{-- Simple link item --}}
    <li>
        @if ($menu->route_name)
            @php
                try {
                    $url = route($menu->route_name, $routeParams);
                } catch (\Exception $e) {
                    $url = '#';
                }
            @endphp
            <a navigate
                class="group flex items-center gap-x-3 py-2.5 px-3.5 {{ $isActive ? $activeClass : $inactiveClass }} text-sm font-light rounded-xl border transition-all duration-200"
                href="{{ $url }}">
                @if ($menu->icon)
                    <span class="relative">
                        @include($menu->icon)
                        @if ($isActive)
                            <span class="absolute -inset-1 bg-blue-500/20 rounded-lg blur-sm"></span>
                        @endif
                    </span>
                @endif
                <span class="relative">{{ $menu->label }}</span>
            </a>
        @else
            <span
                class="group flex items-center gap-x-3 py-2.5 px-3.5 {{ $inactiveClass }} text-sm font-light rounded-xl border transition-all duration-200">
                @if ($menu->icon)
                    <span class="relative">
                        @include($menu->icon)
                    </span>
                @endif
                <span class="relative">{{ $menu->label }}</span>
            </span>
        @endif
    </li>
@else
    {{-- Accordion item with children --}}
    <li class="hs-accordion {{ $isActive ? 'active' : '' }}" id="{{ $accordionId }}">
        <button type="button"
            class="hs-accordion-toggle group w-full text-start flex items-center justify-between gap-x-3 py-2.5 px-3.5 text-sm text-gray-700 font-light rounded-xl border hover:bg-linear-to-r hover:from-gray-100 hover:to-gray-50 border-transparent hover:border-gray-200 transition-all duration-200 dark:text-neutral-300 dark:hover:from-neutral-700/50 dark:hover:to-neutral-800/50 dark:hover:border-neutral-600 cursor-pointer {{ $isActive ? 'bg-linear-to-r from-blue-50 to-indigo-50 border-blue-200 dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-blue-800' : '' }}"
            aria-expanded="{{ $isActive ? 'true' : 'false' }}"
            aria-controls="{{ $accordionId }}-child">
            <div class="flex items-center gap-x-3">
                @if ($menu->icon)
                    <span class="relative">
                        @include($menu->icon)
                        @if ($isActive)
                            <span class="absolute -inset-1 bg-blue-500/20 rounded-lg blur-sm"></span>
                        @endif
                    </span>
                @endif
                <span>{{ $menu->label }}</span>
            </div>
            <svg class="hs-accordion-active:block hidden size-4 transition-transform duration-200"
                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m18 15-6-6 6-6" />
            </svg>
            <svg class="hs-accordion-active:hidden block size-4 transition-transform duration-200"
                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6" />
            </svg>
        </button>

        <div id="{{ $accordionId }}-child"
            class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300 {{ $isActive ? 'block' : 'hidden' }}"
            role="region" aria-labelledby="{{ $accordionId }}">
            <ul class="ps-6 pt-2 space-y-1">
                @foreach ($menu->children as $child)
                    @php
                        $childActive = $child->route_name
                            ? $checkRouteActive($child->route_name, $siblingRoutes)
                            : false;
                        try {
                            $childUrl = $child->route_name ? route($child->route_name, $routeParams) : '#';
                        } catch (\Exception $e) {
                            $childUrl = '#';
                        }
                    @endphp
                    <li>
                        <a navigate
                            class="group flex items-center gap-x-2.5 py-2 px-3.5 text-sm rounded-lg {{ $childActive ? 'bg-blue-50 text-blue-700 font-semibold dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700/50' }} transition-all duration-200"
                            href="{{ $childUrl }}">
                            <span
                                class="size-1.5 rounded-full {{ $childActive ? 'bg-blue-500' : 'bg-gray-300 group-hover:bg-gray-400 dark:bg-neutral-600 dark:group-hover:bg-neutral-500' }} transition-colors"></span>
                            {{ $child->label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </li>
@endif
