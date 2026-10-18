@extends('_admin._layout.app')

@section('title', 'Dashboard')

@section('content')
    @php
        use App\Constants\UserConst;
        use Carbon\Carbon;

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
                            @if (!empty($isPartner))
                                Welcome to your iCanSee Partner Dashboard. Manage registered visually impaired app users and track registration requests.
                            @else
                                Welcome to iCanSee Administration Portal.
                            @endif
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
        @if (!empty($isPartner) && !empty($stats))
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            Registration Requests Overview
                        </h2>
                        <p class="text-xs text-neutral-500">Summary of visually impaired app user registrations submitted by your organization</p>
                    </div>
                    <x-admin.button href="{{ route('admin.app_users.add') }}" size="sm" color="primary">
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
        @endif

        {{-- ADMIN USER STATS OVERVIEW --}}
        @if (empty($isPartner) && !empty($userStats))
            <section class="space-y-6">
                {{-- Section Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200/50 dark:border-blue-800/40">
                                <span class="size-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                Live Analytics
                            </span>
                        </div>
                        <h2 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            Statistik Pengguna
                        </h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Ringkasan performa dan data statistik akun portal serta pengguna tunanetra iCanSee
                        </p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <x-admin.button href="{{ route('admin.users.index') }}" size="sm" color="outline-secondary">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Kelola Pengguna
                        </x-admin.button>
                        <x-admin.button href="{{ route('admin.users.add') }}" size="sm" color="primary">
                            @include('_admin._layout.icons.add')
                            Tambah Pengguna
                        </x-admin.button>
                    </div>
                </div>

                {{-- 4 Stat Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- 1: Total Pengguna Portal --}}
                    <div class="relative overflow-hidden rounded-[24px] bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-white/5 p-5 shadow-sm hover:shadow-md transition duration-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Pengguna Portal
                                </p>
                                <p class="text-3xl font-extrabold text-neutral-900 dark:text-white mt-1 tracking-tight">
                                    {{ number_format($userStats['total'] ?? 0) }}
                                </p>
                            </div>
                            <div class="size-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-neutral-100 dark:border-white/5 flex items-center justify-between text-xs">
                            <span class="text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $userStats['superadmin'] ?? 0 }}</span> Superadmin
                            </span>
                            <span class="text-neutral-300 dark:text-neutral-700">•</span>
                            <span class="text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $userStats['partner'] ?? 0 }}</span> Mitra
                            </span>
                        </div>
                    </div>

                    {{-- 2: Pengguna Aktif --}}
                    <div class="relative overflow-hidden rounded-[24px] bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-white/5 p-5 shadow-sm hover:shadow-md transition duration-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Pengguna Aktif
                                </p>
                                <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 tracking-tight">
                                    {{ number_format($userStats['active'] ?? 0) }}
                                </p>
                            </div>
                            <div class="size-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-neutral-100 dark:border-white/5 flex items-center justify-between text-xs">
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-medium">
                                <span class="size-2 rounded-full bg-emerald-500"></span>
                                {{ ($userStats['total'] ?? 0) > 0 ? round(($userStats['active'] / $userStats['total']) * 100) : 0 }}% Aktif
                            </span>
                            <span class="text-neutral-400 dark:text-neutral-500">
                                {{ $userStats['inactive'] ?? 0 }} Nonaktif
                            </span>
                        </div>
                    </div>

                    {{-- 3: Pengguna Baru Bulan Ini --}}
                    <div class="relative overflow-hidden rounded-[24px] bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-white/5 p-5 shadow-sm hover:shadow-md transition duration-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Baru Bulan Ini
                                </p>
                                <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 tracking-tight">
                                    +{{ number_format($userStats['new_this_month'] ?? 0) }}
                                </p>
                            </div>
                            <div class="size-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-lg shadow-amber-500/20">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-neutral-100 dark:border-white/5 flex items-center text-xs text-neutral-500 dark:text-neutral-400">
                            <span>Registrasi bulan {{ now()->format('F Y') }}</span>
                        </div>
                    </div>

                    {{-- 4: Total App Users (Tunanetra) --}}
                    <div class="relative overflow-hidden rounded-[24px] bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-white/5 p-5 shadow-sm hover:shadow-md transition duration-300">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Pengguna Tunanetra
                                </p>
                                <p class="text-3xl font-extrabold text-purple-600 dark:text-purple-400 mt-1 tracking-tight">
                                    {{ number_format($appUserStats['total'] ?? 0) }}
                                </p>
                            </div>
                            <div class="size-12 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 text-white flex items-center justify-center shadow-lg shadow-purple-500/20">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-neutral-100 dark:border-white/5 flex items-center justify-between text-xs">
                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">
                                {{ $appUserStats['approved'] ?? 0 }} Disetujui
                            </span>
                            <span class="text-neutral-300 dark:text-neutral-700">•</span>
                            <span class="text-amber-600 dark:text-amber-400 font-medium">
                                {{ $appUserStats['pending'] ?? 0 }} Pending
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Two Column Detail Section --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    {{-- Left: Pengguna Terbaru --}}
                    <div class="lg:col-span-7 rounded-[28px] bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-white/5 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                                    Pengguna Portal Terbaru
                                </h3>
                                <p class="text-xs text-neutral-400 mt-0.5">
                                    Daftar akun administrator dan mitra yang baru terdaftar
                                </p>
                            </div>
                            <a navigate href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 flex items-center gap-1 transition">
                                Lihat Semua
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                        <div class="divide-y divide-neutral-100 dark:divide-white/5">
                            @forelse ($userStats['recent_users'] ?? [] as $recentUser)
                                <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between gap-3 group">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="size-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200/80 dark:border-neutral-700 flex items-center justify-center font-bold text-sm text-neutral-700 dark:text-neutral-300 shrink-0">
                                            {{ strtoupper(substr($recentUser->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-neutral-900 dark:text-white truncate">
                                                    {{ $recentUser->name }}
                                                </span>
                                                @if ((int) $recentUser->access_type === UserConst::SUPERADMIN)
                                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/40">
                                                        Superadmin
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 border border-sky-200/60 dark:border-sky-800/40">
                                                        Partner
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-neutral-400 truncate mt-0.5">
                                                {{ $recentUser->email }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        <div class="text-right hidden sm:block">
                                            <span class="inline-flex items-center gap-1 text-xs {{ $recentUser->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-400' }}">
                                                <span class="size-1.5 rounded-full {{ $recentUser->is_active ? 'bg-emerald-500' : 'bg-neutral-400' }}"></span>
                                                {{ $recentUser->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                            <p class="text-[11px] text-neutral-400 mt-0.5">
                                                {{ !empty($recentUser->created_at) ? Carbon::parse($recentUser->created_at)->diffForHumans() : '-' }}
                                            </p>
                                        </div>
                                        <a navigate href="{{ route('admin.users.detail', $recentUser->id) }}" class="p-2 rounded-xl text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/5 transition" title="Detail Pengguna">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-xs text-neutral-400">
                                    Belum ada data pengguna terdaftar.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Right: Status & Distribusi Pengguna --}}
                    <div class="lg:col-span-5 rounded-[28px] bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-white/5 p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-neutral-900 dark:text-white">
                                    Distribusi & Status
                                </h3>
                                <span class="text-xs text-neutral-400">Proporsi Sistem</span>
                            </div>

                            {{-- Role Distribution --}}
                            <div class="space-y-3.5 mb-6">
                                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Peran Pengguna Portal
                                </p>

                                @php
                                    $totalPortal = max($userStats['total'] ?? 1, 1);
                                    $superPct = round((($userStats['superadmin'] ?? 0) / $totalPortal) * 100);
                                    $partnerPct = round((($userStats['partner'] ?? 0) / $totalPortal) * 100);
                                @endphp

                                <div>
                                    <div class="flex justify-between text-xs mb-1.5 font-medium">
                                        <span class="text-neutral-700 dark:text-neutral-300">Super Administrator</span>
                                        <span class="text-neutral-900 dark:text-white font-bold">{{ $userStats['superadmin'] ?? 0 }} ({{ $superPct }}%)</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-neutral-100 dark:bg-white/5 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full" style="width: {{ $superPct }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-xs mb-1.5 font-medium">
                                        <span class="text-neutral-700 dark:text-neutral-300">Mitra / Partner</span>
                                        <span class="text-neutral-900 dark:text-white font-bold">{{ $userStats['partner'] ?? 0 }} ({{ $partnerPct }}%)</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-neutral-100 dark:bg-white/5 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full" style="width: {{ $partnerPct }}%"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- App Users Verification Breakdown --}}
                            <div class="space-y-3 pt-5 border-t border-neutral-100 dark:border-white/5">
                                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Status Pengguna Tunanetra (App)
                                </p>

                                <div class="grid grid-cols-3 gap-2.5">
                                    <div class="p-3 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/40 text-center">
                                        <span class="block text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $appUserStats['approved'] ?? 0 }}</span>
                                        <span class="block text-[11px] font-medium text-emerald-700 dark:text-emerald-300 mt-0.5">Disetujui</span>
                                    </div>
                                    <div class="p-3 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 text-center">
                                        <span class="block text-lg font-bold text-amber-600 dark:text-amber-400">{{ $appUserStats['pending'] ?? 0 }}</span>
                                        <span class="block text-[11px] font-medium text-amber-700 dark:text-amber-300 mt-0.5">Pending</span>
                                    </div>
                                    <div class="p-3 rounded-2xl bg-rose-50/70 dark:bg-rose-950/20 border border-rose-200/60 dark:border-rose-800/40 text-center">
                                        <span class="block text-lg font-bold text-rose-600 dark:text-rose-400">{{ $appUserStats['rejected'] ?? 0 }}</span>
                                        <span class="block text-[11px] font-medium text-rose-700 dark:text-rose-300 mt-0.5">Ditolak</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-neutral-100 dark:border-white/5">
                            <a navigate href="{{ route('admin.app_users.index') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-neutral-100 hover:bg-neutral-200/70 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-semibold text-neutral-800 dark:text-neutral-200 transition">
                                <span>Buka Verifikasi Pengguna Tunanetra</span>
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- MODULES --}}
        <section>
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                        Modul Aplikasi
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