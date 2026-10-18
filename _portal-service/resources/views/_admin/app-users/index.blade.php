@extends('_admin._layout.app')

@section('title', 'App Users (Visual Impairment)')

@php
    use App\Constants\AppUserConst;
@endphp

@section('content')
    <x-admin.page-header :title="'App Users (Visual Impairment)'" subtitle="Registered visually impaired application users & registration requests">
        <x-admin.button href="{{ route('admin.app_users.add') }}" class="font-bold">
            @include('_admin._layout.icons.add')
            Register New User
        </x-admin.button>
    </x-admin.page-header>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-admin.card fit="true">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">{{ number_format($stats['total'] ?? 0) }}</p>
                </div>
                <div class="size-11 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
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
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($stats['pending'] ?? 0) }}</p>
                </div>
                <div class="size-11 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
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
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($stats['approved'] ?? 0) }}</p>
                </div>
                <div class="size-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
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
                    <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ number_format($stats['rejected'] ?? 0) }}</p>
                </div>
                <div class="size-11 rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </x-admin.card>
    </div>

    {{-- Filter Bar --}}
    <div class="mb-6">
        <form action="{{ route('admin.app_users.index') }}" method="GET" navigate-form
            class="flex flex-col md:flex-row items-center gap-3">
            <div class="w-full md:w-64">
                <x-admin.input name="keywords" :value="$keywords ?? ''" placeholder="Search name, phone, NIK..." size="sm" />
            </div>
            <div class="w-full md:w-48">
                @php
                    $statusOptions = ['all' => 'All Statuses'] + $statuses;
                @endphp
                <x-admin.select :label="null" name="approval_status" :options="$statusOptions" :value="$approval_status ?? 'all'" size="sm" class="cursor-pointer" />
            </div>
            <div class="w-full md:w-48">
                @php
                    $levelOptions = ['all' => 'All Impairment Levels'] + $levels;
                @endphp
                <x-admin.select :label="null" name="visual_impairment_level" :options="$levelOptions" :value="$visual_impairment_level ?? 'all'" size="sm" class="cursor-pointer" />
            </div>
            <div class="flex items-center gap-2 self-start md:self-auto">
                <x-admin.button type="submit" size="sm" color="primary">
                    @include('_admin._layout.icons.search')
                    Search
                </x-admin.button>
                @if (!empty($keywords) || ($approval_status ?? 'all') !== 'all' || ($visual_impairment_level ?? 'all') !== 'all')
                    <x-admin.button href="{{ route('admin.app_users.index') }}" size="sm" color="outline-secondary">
                        @include('_admin._layout.icons.reset')
                        Reset
                    </x-admin.button>
                @endif
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <x-admin.table.wrapper>
        <x-admin.table>
            <x-admin.table.thead>
                <tr>
                    <x-admin.table.th>App User</x-admin.table.th>
                    <x-admin.table.th>Visual Impairment</x-admin.table.th>
                    <x-admin.table.th>Guardian Contact</x-admin.table.th>
                    <x-admin.table.th>Registration Status</x-admin.table.th>
                    <x-admin.table.th align="end">Actions</x-admin.table.th>
                </tr>
            </x-admin.table.thead>
            <x-admin.table.tbody>
                @forelse($data as $d)
                    <x-admin.table.tr>
                        <x-admin.table.td>
                            <div class="flex items-center gap-x-3">
                                <span class="inline-flex items-center justify-center size-9.5 rounded-full bg-blue-50 border border-blue-200 dark:bg-blue-900/30 dark:border-blue-800 text-blue-600 dark:text-blue-400 font-semibold text-sm">
                                    {{ strtoupper(substr($d->name, 0, 1)) }}
                                </span>
                                <div>
                                    <span class="block text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                        {{ $d->name }}
                                        @if(!empty($d->nickname))
                                            <span class="text-xs font-normal text-neutral-400">({{ $d->nickname }})</span>
                                        @endif
                                    </span>
                                    <span class="block text-xs text-neutral-500">
                                        {{ $d->phone_number ?: ($d->email ?: 'No contact details') }}
                                    </span>
                                    @if(!empty($d->city))
                                        <span class="block text-[11px] text-neutral-400">{{ $d->city }}, {{ $d->province }}</span>
                                    @endif
                                </div>
                            </div>
                        </x-admin.table.td>

                        <x-admin.table.td>
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium {{ (int)$d->visual_impairment_level === AppUserConst::LEVEL_TOTAL_BLIND ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300' : 'bg-sky-50 text-sky-700 dark:bg-sky-900/20 dark:text-sky-300' }}">
                                    {{ AppUserConst::getVisualImpairmentLevelLabel($d->visual_impairment_level) }}
                                </span>
                                @if(!empty($d->visual_impairment_cause))
                                    <span class="block text-xs text-neutral-400 italic">Cause: {{ $d->visual_impairment_cause }}</span>
                                @endif
                            </div>
                        </x-admin.table.td>

                        <x-admin.table.td>
                            @if(!empty($d->guardian_name))
                                <span class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $d->guardian_name }}</span>
                                <span class="block text-xs text-neutral-400">
                                    {{ $d->guardian_relation ? "({$d->guardian_relation}) " : '' }}{{ $d->guardian_phone }}
                                </span>
                            @else
                                <span class="text-xs text-neutral-400">-</span>
                            @endif
                        </x-admin.table.td>

                        <x-admin.table.td>
                            @if ((int) $d->approval_status === AppUserConst::STATUS_APPROVED)
                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                    Approved
                                </span>
                            @elseif ((int) $d->approval_status === AppUserConst::STATUS_REJECTED)
                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400" title="{{ $d->rejection_reason ?? 'Rejected' }}">
                                    <span class="size-1.5 rounded-full bg-rose-500"></span>
                                    Rejected
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                    <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Pending Review
                                </span>
                            @endif
                        </x-admin.table.td>

                        <x-admin.table.td innerClass="px-6 py-2 flex items-center justify-end gap-x-1.5">
                            <a navigate
                                class="inline-flex items-center justify-center size-8 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-100 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white dark:hover:bg-neutral-700"
                                href="{{ route('admin.app_users.detail', $d->id) }}" title="View Details">
                                @include('_admin._layout.icons.view_detail')
                            </a>
                            <a navigate
                                class="inline-flex items-center justify-center size-8 text-sm font-semibold rounded-lg border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:border-blue-300 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-500 dark:hover:bg-blue-800/30"
                                href="{{ route('admin.app_users.update', $d->id) }}" title="Edit">
                                @include('_admin._layout.icons.pencil')
                            </a>
                            <button type="button"
                                class="inline-flex items-center justify-center size-8 text-sm font-semibold rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 hover:border-red-300 dark:border-red-800 dark:bg-red-900/20 dark:text-red-500 dark:hover:bg-red-800/30 cursor-pointer"
                                title="Delete" data-hs-overlay="#delete-modal"
                                onclick="setDeleteData('{{ $d->id }}', '{{ addslashes($d->name) }}')">
                                @include('_admin._layout.icons.trash')
                            </button>
                        </x-admin.table.td>
                    </x-admin.table.tr>
                @empty
                    <x-admin.table.tr>
                        <x-admin.table.td colspan="5" class="py-12">
                            <x-admin.empty-state message="No app users or registration requests found." />
                        </x-admin.table.td>
                    </x-admin.table.tr>
                @endforelse
            </x-admin.table.tbody>
        </x-admin.table>
    </x-admin.table.wrapper>

    @if (method_exists($data, 'hasPages') && $data->hasPages())
        <div class="mt-4">
            {{ $data->links() }}
        </div>
    @endif

    {{-- Delete Modal --}}
    <x-admin.modal id="delete-modal" title="Confirm Deletion">
        <form id="delete-form" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="p-4 sm:p-6 text-center">
                <div class="size-14 rounded-full bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 mx-auto flex items-center justify-center mb-4">
                    @include('_admin._layout.icons.warning_modal')
                </div>
                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Delete App User</h3>
                <p class="text-sm text-neutral-500">
                    Are you sure you want to delete <span id="delete-user-name" class="font-semibold text-neutral-800 dark:text-white"></span>? This action can be undone by an administrator.
                </p>
                <div class="mt-6 flex justify-center gap-x-3">
                    <x-admin.button type="button" color="secondary" data-hs-overlay="#delete-modal">
                        Cancel
                    </x-admin.button>
                    <x-admin.button type="submit" color="danger">
                        Delete
                    </x-admin.button>
                </div>
            </div>
        </form>
    </x-admin.modal>
@endsection

@push('scripts')
    <script>
        function setDeleteData(id, name) {
            const form = document.getElementById('delete-form');
            form.action = `{{ url('admin/app-users/delete') }}/${id}`;
            document.getElementById('delete-user-name').innerText = name;
        }
    </script>
@endpush
