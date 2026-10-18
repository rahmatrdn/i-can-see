@extends('_admin._layout.app')

@section('title', 'Partner Dashboard')

@section('content')
    @php
        $today = now();

        $greeting = match (true) {
            $today->hour < 12 => 'Good Morning',
            $today->hour < 17 => 'Good Afternoon',
            default => 'Good Evening',
        };

        $accentPalette = ['blue', 'emerald', 'purple', 'orange', 'indigo', 'violet'];

        $accent = [
            'blue' => [
                'bg' => 'from-blue-500/10 via-blue-500/5 to-transparent',
                'icon' => 'bg-gradient-to-br from-blue-500 to-blue-600 text-white',
                'icon_shadow' => 'shadow-blue-500/25',
                'arrow_hover' => 'group-hover:text-blue-400',
                'glow' => 'group-hover:shadow-blue-500/10',
            ],
            'emerald' => [
                'bg' => 'from-emerald-500/10 via-emerald-500/5 to-transparent',
                'icon' => 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white',
                'icon_shadow' => 'shadow-emerald-500/25',
                'arrow_hover' => 'group-hover:text-emerald-400',
                'glow' => 'group-hover:shadow-emerald-500/10',
            ],
            'purple' => [
                'bg' => 'from-purple-500/10 via-purple-500/5 to-transparent',
                'icon' => 'bg-gradient-to-br from-purple-500 to-purple-600 text-white',
                'icon_shadow' => 'shadow-purple-500/25',
                'arrow_hover' => 'group-hover:text-purple-400',
                'glow' => 'group-hover:shadow-purple-500/10',
            ],
            'orange' => [
                'bg' => 'from-orange-500/10 via-orange-500/5 to-transparent',
                'icon' => 'bg-gradient-to-br from-orange-500 to-orange-600 text-white',
                'icon_shadow' => 'shadow-orange-500/25',
                'arrow_hover' => 'group-hover:text-orange-400',
                'glow' => 'group-hover:shadow-orange-500/10',
            ],
            'indigo' => [
                'bg' => 'from-indigo-500/10 via-indigo-500/5 to-transparent',
                'icon' => 'bg-gradient-to-br from-indigo-500 to-indigo-600 text-white',
                'icon_shadow' => 'shadow-indigo-500/25',
                'arrow_hover' => 'group-hover:text-indigo-400',
                'glow' => 'group-hover:shadow-indigo-500/10',
            ],
            'violet' => [
                'bg' => 'from-violet-500/10 via-violet-500/5 to-transparent',
                'icon' => 'bg-gradient-to-br from-violet-500 to-violet-600 text-white',
                'icon_shadow' => 'shadow-violet-500/25',
                'arrow_hover' => 'group-hover:text-violet-400',
                'glow' => 'group-hover:shadow-violet-500/10',
            ],
        ];
    @endphp

    <div class="space-y-8">

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[32px] bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-white/5 shadow-[0_10px_60px_rgba(0,0,0,0.06)] mb-8">
            {{-- Background Decoration --}}
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-0 right-0 w-[420px] h-[420px] bg-blue-500/10 blur-3xl rounded-full"
                     style="animation: blob1 8s ease-in-out infinite;"></div>
                <div class="absolute bottom-0 left-0 w-[320px] h-[320px] bg-indigo-500/10 blur-3xl rounded-full"
                     style="animation: blob2 10s ease-in-out infinite;"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] bg-violet-500/5 blur-3xl rounded-full"
                     style="animation: blob3 14s ease-in-out infinite;"></div>
            </div>

            <div class="relative px-8 lg:px-10 py-10 lg:py-12">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-10">

                    {{-- LEFT --}}
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-neutral-100 dark:bg-white/5 mb-5">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                {{ $today->format('l, d F Y') }}
                            </span>
                        </div>

                        <h1 class="text-4xl lg:text-5xl font-semibold tracking-tight text-neutral-900 dark:text-white leading-tight">
                            {{ $greeting }},
                            <span class="bg-gradient-to-r from-blue-600 to-indigo-500 bg-clip-text text-transparent">
                                {{ $partner->name ?? Auth::user()->name }}
                            </span>
                        </h1>

                        <p class="mt-4 text-[15px] leading-7 text-neutral-500 dark:text-neutral-400 max-w-xl">
                            Welcome to the iCanSee Partner Portal. Manage registered visually impaired users and track the approval status of your submitted requests.
                        </p>
                    </div>

                    {{-- RIGHT --}}
                    <div class="flex flex-col items-start lg:items-end">
                        <div class="text-[72px] leading-none font-light tracking-[-4px] text-neutral-900 dark:text-white tabular-nums"
                             id="dashboard-clock">
                            {{ $today->format('H:i') }}
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- PARTNER STATS OVERVIEW --}}
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                        Registration Requests Overview
                    </h2>
                    <p class="text-xs text-neutral-500">Summary of visually impaired app user registrations submitted by your organization</p>
                </div>
                <x-admin.button href="{{ route('partner.app_users.add') }}" size="sm" color="primary">
                    @include('_admin._layout.icons.add')
                    Register App User
                </x-admin.button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-admin.card fit="true">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Total Users</p>
                            <p class="text-3xl font-bold text-neutral-900 dark:text-white mt-1">{{ number_format($stats['total'] ?? 0) }}</p>
                        </div>
                        <div class="size-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card fit="true">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Pending Review</p>
                            <p class="text-3xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($stats['pending'] ?? 0) }}</p>
                        </div>
                        <div class="size-12 rounded-2xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card fit="true">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Approved</p>
                            <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($stats['approved'] ?? 0) }}</p>
                        </div>
                        <div class="size-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card fit="true">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Rejected</p>
                            <p class="text-3xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ number_format($stats['rejected'] ?? 0) }}</p>
                        </div>
                        <div class="size-12 rounded-2xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </section>

        {{-- MODULES --}}
        <section>
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                        Partner Modules
                    </h2>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @forelse ($modules as $index => $menu)
                    @php
                        $accentKey = $accentPalette[$index % count($accentPalette)];
                        $c = $accent[$accentKey];

                        try {
                            $moduleUrl = route($menu->route_name);
                        } catch (\Exception $e) {
                            $moduleUrl = '#';
                        }

                        $moduleDesc = ! empty($menu->children)
                            ? collect($menu->children)->pluck('label')->join(', ')
                            : 'Access module ' . $menu->label . '.';
                    @endphp

                    <a navigate
                       href="{{ $moduleUrl }}"
                       class="group relative overflow-hidden rounded-[28px] bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-white/5 p-7 transition-all duration-500 hover:-translate-y-1 hover:shadow-lg shadow-black/5 {{ $c['glow'] }}">

                        {{-- Gradient Layer --}}
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition duration-500 bg-gradient-to-br {{ $c['bg'] }}"></div>

                        <div class="relative flex flex-col h-full">

                            {{-- TOP --}}
                            <div class="flex items-start justify-between">
                                <div class="size-14 rounded-2xl {{ $c['icon'] }} flex items-center justify-center shadow-lg {{ $c['icon_shadow'] }} ring-1 ring-white/20 ring-inset transition-all duration-300 group-hover:shadow-xl group-hover:scale-[1.03] [&_svg]:size-6">
                                    @if ($menu->icon)
                                        @include($menu->icon)
                                    @endif
                                </div>

                                <div class="translate-x-0 group-hover:translate-x-1 transition duration-300">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="size-5 text-neutral-300 dark:text-neutral-600 transition-colors {{ $c['arrow_hover'] }}"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </div>
                            </div>

                            {{-- CONTENT --}}
                            <div class="mt-8">
                                <h3 class="text-2xl font-semibold tracking-tight text-neutral-900 dark:text-white">
                                    {{ $menu->label }}
                                </h3>
                                <p class="mt-1 text-md text-neutral-400 dark:text-neutral-400">
                                    {{ $moduleDesc }}
                                </p>
                            </div>

                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-[28px] border border-dashed border-neutral-200 dark:border-neutral-700 p-10 text-center">
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            No modules currently available for your account.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

    </div>

    @push('scripts')
        <style>
            @keyframes blob1 {
                0%   { transform: translateX(0px); }
                20%  { transform: translateX(60px); }
                45%  { transform: translateX(-30px); }
                65%  { transform: translateX(80px); }
                80%  { transform: translateX(-50px); }
                100% { transform: translateX(0px); }
            }
            @keyframes blob2 {
                0%   { transform: translateX(0px); }
                25%  { transform: translateX(-70px); }
                50%  { transform: translateX(40px); }
                70%  { transform: translateX(-55px); }
                90%  { transform: translateX(30px); }
                100% { transform: translateX(0px); }
            }
            @keyframes blob3 {
                0%   { transform: translate(-50%, -50%) translateX(0px); }
                30%  { transform: translate(-50%, -50%) translateX(50px); }
                55%  { transform: translate(-50%, -50%) translateX(-60px); }
                75%  { transform: translate(-50%, -50%) translateX(35px); }
                100% { transform: translate(-50%, -50%) translateX(0px); }
            }
        </style>
        <script>
            (() => {
                const clock = document.getElementById('dashboard-clock');
                if (!clock) return;

                setInterval(() => {
                    const now = new Date();
                    clock.textContent = now.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                }, 1000);
            })();
        </script>
    @endpush

@endsection
