@extends('_admin._layout.app')

@section('title', 'App User Details - ' . $data->name)

@php
    use App\Constants\AppUserConst;
@endphp

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <x-admin.page-header :title="$data->name" :subtitle="'Registered visually impaired app user details'" :backUrl="route('partner.app_users.index')">
            <div class="flex items-center gap-2">
                @if ((int) $data->approval_status === AppUserConst::STATUS_REJECTED)
                    <x-admin.button type="button" color="success" data-hs-overlay="#resubmit-modal">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                            <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                            <path d="M16 21h5v-5"/>
                        </svg>
                        Re-submit Registration
                    </x-admin.button>
                @endif
                <x-admin.button href="{{ route('partner.app_users.update', $data->id) }}" color="outline-secondary">
                    @include('_admin._layout.icons.pencil')
                    Edit User
                </x-admin.button>
            </div>
        </x-admin.page-header>

        {{-- Status Notice Banner --}}
        @if ((int) $data->approval_status === AppUserConst::STATUS_REJECTED)
            <x-admin.card class="border-rose-200 bg-rose-50/70 dark:border-rose-900/40 dark:bg-rose-950/20">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 shrink-0">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="15" y1="9" x2="9" y2="15"/>
                                <line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-base text-rose-900 dark:text-rose-200">Registration Rejected</h4>
                            <p class="text-sm text-rose-700 dark:text-rose-300/90 mt-0.5">
                                Rejection Reason: <span class="font-medium text-rose-900 dark:text-rose-200">{{ $data->rejection_reason ?: 'No reason specified' }}</span>
                            </p>
                            <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                                You can update the registration details if needed, then re-submit this registration for administrator review.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                        <x-admin.button href="{{ route('partner.app_users.update', $data->id) }}" color="outline-secondary">
                            Edit Details
                        </x-admin.button>
                        <x-admin.button type="button" color="success" data-hs-overlay="#resubmit-modal">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                                <path d="M16 21h5v-5"/>
                            </svg>
                            Re-submit
                        </x-admin.button>
                    </div>
                </div>
            </x-admin.card>
        @elseif ((int) $data->approval_status === AppUserConst::STATUS_PENDING)
            <x-admin.card class="border-amber-200 bg-amber-50/60 dark:border-amber-900/40 dark:bg-amber-950/20">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 shrink-0">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-base text-amber-900 dark:text-amber-200">Pending Administrator Approval</h4>
                        <p class="text-sm text-amber-700 dark:text-amber-300/90 mt-0.5">
                            This registration request has been submitted and is currently being reviewed by administrators.
                        </p>
                    </div>
                </div>
            </x-admin.card>
        @elseif ((int) $data->approval_status === AppUserConst::STATUS_APPROVED)
            <x-admin.card class="border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 shrink-0">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-base text-emerald-900 dark:text-emerald-200">Registration Approved</h4>
                        <p class="text-sm text-emerald-700 dark:text-emerald-300/90 mt-0.5">
                            This registration has been verified and approved by administrators.
                        </p>
                    </div>
                </div>
            </x-admin.card>
        @endif

        {{-- Status and Partner Info banner --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-admin.card fit="true">
                <p class="text-xs font-medium text-neutral-400 uppercase tracking-wider">Registration Status</p>
                <div class="mt-2">
                    @if ((int) $data->approval_status === AppUserConst::STATUS_APPROVED)
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            Approved
                        </span>
                    @elseif ((int) $data->approval_status === AppUserConst::STATUS_REJECTED)
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
                            <span class="size-1.5 rounded-full bg-rose-500"></span>
                            Rejected
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                            <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Pending Approval
                        </span>
                    @endif
                </div>
            </x-admin.card>

            <x-admin.card fit="true">
                <p class="text-xs font-medium text-neutral-400 uppercase tracking-wider">Impairment Level</p>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-md text-xs font-semibold {{ (int)$data->visual_impairment_level === AppUserConst::LEVEL_TOTAL_BLIND ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-300' }}">
                        {{ AppUserConst::getVisualImpairmentLevelLabel($data->visual_impairment_level) }}
                    </span>
                </div>
            </x-admin.card>

            <x-admin.card fit="true">
                <p class="text-xs font-medium text-neutral-400 uppercase tracking-wider">Registered By Partner</p>
                <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mt-2">
                    {{ $data->partner_name ?: 'Direct / System' }}
                    @if (!empty($data->partner_type))
                        <span class="text-xs font-normal text-neutral-400 block capitalize">({{ $data->partner_type }})</span>
                    @endif
                </p>
            </x-admin.card>
        </div>

        {{-- Main Detail Card --}}
        <x-admin.card>
            <div class="space-y-8">
                {{-- 1. Personal Details --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        1. Personal Details
                    </h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mt-4 text-sm">
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Full Name</dt>
                            <dd class="mt-1 font-semibold text-neutral-800 dark:text-neutral-200">{{ $data->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Preferred Nickname</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->nickname ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">National Identity Number (NIK)</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200 font-mono">{{ $data->national_id_number ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Gender</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ AppUserConst::getGenderLabel($data->gender) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Date of Birth</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->date_of_birth ? \Carbon\Carbon::parse($data->date_of_birth)->format('d F Y') : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Phone Number</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->phone_number ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Email Address</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->email ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- 2. Visual Impairment Details --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        2. Visual Impairment Details
                    </h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mt-4 text-sm">
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Impairment Level</dt>
                            <dd class="mt-1 font-semibold text-neutral-800 dark:text-neutral-200">
                                {{ AppUserConst::getVisualImpairmentLevelLabel($data->visual_impairment_level) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Impairment Cause</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->visual_impairment_cause ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- 3. Address Information --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        3. Address
                    </h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mt-4 text-sm">
                        <div class="md:col-span-2">
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Street Address</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->address ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">City / Regency</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->city ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Province</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->province ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- 4. Guardian Details --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        4. Guardian & Emergency Contact
                    </h3>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 mt-4 text-sm">
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Guardian Name</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->guardian_name ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Relationship</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->guardian_relation ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-400 uppercase font-medium">Guardian Phone</dt>
                            <dd class="mt-1 text-neutral-800 dark:text-neutral-200">{{ $data->guardian_phone ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- 5. Additional Notes --}}
                @if (!empty($data->notes))
                    <div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                            5. Notes & Observations
                        </h3>
                        <div class="mt-3 p-3.5 rounded-xl bg-neutral-50 dark:bg-neutral-900 text-sm text-neutral-700 dark:text-neutral-300">
                            {{ $data->notes }}
                        </div>
                    </div>
                @endif

                {{-- Audit / Timestamps --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex flex-wrap items-center justify-between text-xs text-neutral-400 gap-2">
                    <div>
                        Submitted at: {{ $data->created_at ? \Carbon\Carbon::parse($data->created_at)->format('d M Y, H:i') : '-' }}
                    </div>
                    @if (!empty($data->updated_at))
                        <div>
                            Last modified: {{ \Carbon\Carbon::parse($data->updated_at)->format('d M Y, H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        </x-admin.card>
    </div>

    {{-- Re-submit Modal --}}
    <x-admin.modal id="resubmit-modal" title="Re-submit Registration" :formAction="route('partner.app_users.resubmit', $data->id)">
        <div class="space-y-4">
            <p class="text-sm text-neutral-600 dark:text-neutral-300">
                Are you ready to re-submit the registration request for <strong class="text-neutral-900 dark:text-white">{{ $data->name }}</strong>?
            </p>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                Re-submitting will change the status back to <strong>Pending Approval</strong> and notify the administrators for re-evaluation.
            </p>

            <div>
                <label for="resubmission_note" class="block text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-1.5">
                    Notes for Reviewer <span class="text-xs font-normal text-neutral-400">(Optional)</span>
                </label>
                <textarea
                    id="resubmission_note"
                    name="resubmission_note"
                    rows="3"
                    class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:focus:ring-blue-600"
                    placeholder="Describe what has been updated or corrected (e.g. Corrected guardian phone number, updated address)...">{{ old('resubmission_note') }}</textarea>
                @error('resubmission_note')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-slot:footer>
            <button type="button"
                class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-xl border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-hidden dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 cursor-pointer"
                data-hs-overlay="#resubmit-modal">
                Cancel
            </button>
            <x-admin.button type="submit" color="success">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                    <path d="M16 21h5v-5"/>
                </svg>
                Confirm & Re-submit
            </x-admin.button>
        </x-slot:footer>
    </x-admin.modal>
@endsection
