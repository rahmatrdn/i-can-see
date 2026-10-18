<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ env('APP_ENV') == 'local' ? '[LOCAL] ' : '' }}@hasSection('title')@yield('title') - @endif iCanSee</title>

    {{-- Favicon --}}
    @include('_admin._layout.favicon')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin-custom.css', 'resources/js/admin-custom.js'])

    <!-- NProgress -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />

</head>

<body>
    <!-- ========== HEADER ========== -->
    <header
        class="inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-[48] w-full bg-white border-b border-gray-200 text-sm py-1 lg:py-0 xl:py-0 lg:ps-65 dark:bg-neutral-800 dark:border-neutral-700 fixed top-0 left-0 right-0 shadow-lg">
        <nav class="px-4 sm:px-2 flex basis-full items-center w-full mx-auto">
            <div class="me-1 lg:me-0 lg:hidden flex items-center">
                <!-- Navigation Toggle -->
                <button type="button"
                    class="me-0 size-8 px-3 flex justify-center items-center gap-x-2 border border-gray-200 text-gray-800 hover:text-gray-500 rounded-lg focus:outline-hidden focus:text-gray-500 disabled:opacity-50 disabled:pointer-events-none dark:border-neutral-700 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500 shadow-lg"
                    aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-application-sidebar"
                    aria-label="Toggle navigation" data-hs-overlay="#hs-application-sidebar">
                    <span class="sr-only">Toggle Navigation</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" />
                        <path d="M15 3v18" />
                        <path d="m8 9 3 3-3 3" />
                    </svg>
                </button>
                <!-- End Navigation Toggle -->
                <!-- Logo -->
                @php
                    $headerDashboardRoute = Auth::check() && (int) Auth::user()->access_type === \App\Constants\UserConst::PARTNER ? 'partner.dashboard' : 'admin.dashboard';
                @endphp
                <a class="flex-none rounded-md text-xl inline-block font-semibold focus:outline-hidden focus:opacity-80"
                    href="{{ route($headerDashboardRoute) }}" aria-label="iCanSee">
                    <img src="{{ asset('images/logo-icansee.png') }}" alt="iCanSee" class="w-32 h-auto p-2">
                </a>
                <!-- End Logo -->

            </div>

            <div class="w-full flex items-center justify-end ms-auto gap-x-1 md:gap-x-3">


            </div>
        </nav>
    </header>
    <div class="mt-16 sm:mt-0"></div>
    <!-- ========== END HEADER ========== -->

    @include('_admin._layout.sidebar.sidebar')

    <!-- Content -->
    <div class="w-full lg:ps-64 bg-gray-50 dark:bg-neutral-900 min-h-screen">
        <div id="main-content" class="space-y-6 px-6 py-8 sm:px-8 sm:py-10 max-w-screen-2xl mx-auto w-full">
            @if (session('success'))
                <div id="spa-flash-success" style="display: none;">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div id="spa-flash-error" style="display: none;">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
    <!-- End Content -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- NProgress -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

    @stack('scripts')

</body>

</html>
