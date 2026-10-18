@extends('_admin._layout.app')

@section('title', 'Detail Pengguna')

@php
    use App\Constants\UserConst;
@endphp

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white overflow-hidden shadow-lg rounded-2xl dark:bg-neutral-800">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center">
                <a href="{{ route('admin.users.index') }}"
                    class="py-3 px-3 inline-flex items-center gap-x-2 text-xl rounded-xl border border-gray-200 bg-white text-gray-800 shadow-md hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 cursor-pointer">
                    <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="90" height="90"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7" />
                        <path d="M19 12H5" />
                    </svg>
                </a>
                <div class="ms-3">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                        Detail Data Pengguna
                    </h2>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-center gap-x-6 mb-8">
                    <div
                        class="inline-flex items-center justify-center size-20 rounded-full bg-blue-100 text-blue-500 text-3xl font-bold dark:bg-blue-800/30 dark:text-blue-400">
                        {{ strtoupper(substr($data->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $data->name }}</h3>
                        <p class="text-gray-500 dark:text-neutral-400">{{ $data->email }}</p>
                        <div class="mt-2">
                            <span
                                class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-200 uppercase">
                                {{ UserConst::getAccessTypes()[$data->access_type] ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div
                        class="p-4 bg-gray-50 rounded-xl dark:bg-neutral-700/50 border border-gray-100 dark:border-neutral-700">
                        <p class="text-xs text-gray-500 dark:text-neutral-400 uppercase tracking-wide font-semibold mb-1">
                            Dibuat Pada</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">
                            {{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y, H:i') }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-neutral-500 mt-0.5">
                            {{ \Carbon\Carbon::parse($data->created_at)->diffForHumans() }}
                        </p>
                    </div>

                    @if (!empty($data->updated_at))
                        <div
                            class="p-4 bg-gray-50 rounded-xl dark:bg-neutral-700/50 border border-gray-100 dark:border-neutral-700">
                            <p
                                class="text-xs text-gray-500 dark:text-neutral-400 uppercase tracking-wide font-semibold mb-1">
                                Terakhir Diupdate</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">
                                {{ \Carbon\Carbon::parse($data->updated_at)->translatedFormat('d F Y, H:i') }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-neutral-500 mt-0.5">
                                {{ \Carbon\Carbon::parse($data->updated_at)->diffForHumans() }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
