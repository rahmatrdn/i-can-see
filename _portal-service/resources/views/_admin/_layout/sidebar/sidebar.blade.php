@php
    use App\Constants\UserConst;

    $user = Auth::user();
    $dashboardRoute = match ($user?->access_type) {
        UserConst::PARTNER => 'partner.dashboard',
        default => 'admin.dashboard',
    };
@endphp

<div id="hs-application-sidebar"
    class="hs-overlay  [--auto-close:lg]
  hs-overlay-open:translate-x-0
  -translate-x-full transition-all duration-300 transform
  w-65 h-full
  hidden
  fixed inset-y-0 start-0 z-60
  lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
  dark:bg-neutral-800 dark:border-neutral-700
  bg-white shadow-xl shadow-gray-300/20"
    role="dialog" tabindex="-1" aria-label="Sidebar">
    <div class="relative flex flex-col h-full max-h-full">
        <div class="px-2 pt-0 flex items-center bg-primary/3">
            <a class="flex-none rounded-xl text-xl inline-block font-semibold focus:outline-hidden focus:opacity-80"
                href="{{ route($dashboardRoute) }}" aria-label="iCanSee">
                <img src="{{ asset('images/logo-icansee.png') }}" alt="iCanSee" class="w-44 h-auto p-4">
            </a>
        </div>

        <div
            class="flex-1 overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500 mt-4">
            <nav class="hs-accordion-group p-4 w-full flex flex-col flex-wrap">
                <ul class="flex flex-col space-y-1.5">

                    @include('_admin._layout.sidebar.sidebar_utama')
                </ul>
            </nav>
        </div>

        <div
            class="p-4 border-t border-gray-200/80 dark:border-neutral-700/80 sticky bottom-0 z-10 bg-white/95 backdrop-blur-sm dark:bg-neutral-800/95">
            <div class="hs-dropdown relative inline-flex w-full [--placement:top-left]">
                <button id="sidebar-bottom-dropdown" type="button"
                    class="hs-dropdown-toggle w-full group flex items-center gap-x-3 py-2.5 px-3.5 text-start text-sm rounded-xl border border-gray-200/50 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 dark:border-neutral-700 dark:bg-neutral-800/50 dark:hover:bg-neutral-700 dark:hover:border-neutral-600"
                    aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                    <div class="relative">
                        <img class="shrink-0 size-10 rounded-full ring-2 ring-white shadow-sm"
                            src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&length=2"
                            alt="Avatar">
                        <span
                            class="absolute bottom-0 right-0 size-3 bg-green-500 border-2 border-white rounded-full dark:border-neutral-800"></span>
                    </div>
                    <div class="grow min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200 truncate">
                            {{ $user->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-neutral-500">
                            {{ UserConst::getAccessTypes()[$user->access_type] ?? 'Unknown' }}
                        </p>
                    </div>
                    <svg class="size-4 text-gray-400 group-hover:text-gray-600 transition-colors dark:text-neutral-500 dark:group-hover:text-neutral-300"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>

                <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-lg rounded-xl mb-2 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full"
                    role="menu" aria-orientation="vertical" aria-labelledby="sidebar-bottom-dropdown">
                    <div class="p-1.5 space-y-0.5">
                        <!-- Switch/Toggle -->
                        <div
                            class="px-3 py-2.5 flex items-center justify-between border-b border-gray-100 dark:border-neutral-700 mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-neutral-300">Theme</span>
                            <div class="flex items-center gap-x-1 p-1 bg-gray-100 rounded-lg dark:bg-neutral-700">
                                <button type="button"
                                    class="hs-dark-mode hs-dark-mode-active:hidden flex shrink-0 justify-center items-center gap-x-1.5 px-2.5 py-1 text-xs font-medium text-gray-600 hover:text-gray-900 focus:outline-hidden rounded-md transition-all dark:text-neutral-400 dark:hover:text-neutral-200"
                                    data-hs-theme-click-value="dark">
                                    @include('_admin._layout.icons.sidebar.theme_dark')
                                    Dark
                                </button>
                                <button type="button"
                                    class="hs-dark-mode hs-dark-mode-active:flex hidden shrink-0 justify-center items-center gap-x-1.5 px-2.5 py-1 text-xs font-medium text-gray-600 hover:text-gray-900 focus:outline-hidden rounded-md transition-all dark:text-neutral-400 dark:hover:text-neutral-200"
                                    data-hs-theme-click-value="light">
                                    @include('_admin._layout.icons.sidebar.theme_light')
                                    Light
                                </button>
                            </div>
                        </div>
                        <a navigate
                            class="flex items-center gap-x-3 py-2.5 px-3 rounded-lg text-sm text-gray-700 hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 transition-colors dark:text-neutral-300 dark:hover:bg-neutral-700/50 dark:focus:bg-neutral-700/50"
                            href="{{ route('admin.profile.change_password') }}">
                            @include('_admin._layout.icons.sidebar.change-password')
                            Ubah Password
                        </a>
                        <form action="{{ route('logout') }}" method="POST"
                            onsubmit="return confirm('Apakah anda yakin ingin keluar?');">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-x-3 py-2.5 px-3 rounded-lg text-sm text-red-600 hover:bg-red-50 focus:outline-hidden focus:bg-red-50 transition-colors dark:text-red-400 dark:hover:bg-red-900/20 dark:focus:bg-red-900/20">
                                @include('_admin._layout.icons.sidebar.logout')
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebarAccordions = document.querySelectorAll('#hs-application-sidebar .hs-accordion-toggle');
        const scrollContainer = document.querySelector('#hs-application-sidebar .overflow-y-auto');

        if (sidebarAccordions.length > 0 && scrollContainer) {
            sidebarAccordions.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    const accordion = toggle.closest('.hs-accordion');

                    if (accordion && !accordion.classList.contains('active')) {
                        // Tunggu animasi expand selesai (Preline biasanya ~300ms)
                        setTimeout(() => {
                            const accordionRect = accordion.getBoundingClientRect();
                            const containerRect = scrollContainer
                                .getBoundingClientRect();

                            // Jika bagian bawah accordion melebihi bagian bawah container yang terlihat
                            if (accordionRect.bottom > containerRect.bottom) {
                                // Hitung selisihnya dan tambahkan sedikit jarak (padding)
                                const scrollDistance = accordionRect.bottom -
                                    containerRect.bottom + 20;

                                scrollContainer.scrollBy({
                                    top: scrollDistance,
                                    behavior: 'smooth'
                                });
                            }
                        }, 350);
                    }
                });
            });
        }

    });
</script>
