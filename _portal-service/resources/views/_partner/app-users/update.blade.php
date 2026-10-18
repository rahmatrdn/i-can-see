@extends('_admin._layout.app')

@section('title', 'Edit App User - ' . $data->name)

@php
    use App\Constants\AppUserConst;
@endphp

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <x-admin.page-header :title="'Edit App User'" :subtitle="'Update registration request for ' . $data->name" :backUrl="route('partner.app_users.index')">
            @if ((int) $data->approval_status === AppUserConst::STATUS_APPROVED)
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    Approved
                </span>
            @elseif ((int) $data->approval_status === AppUserConst::STATUS_REJECTED)
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
                    <span class="size-1.5 rounded-full bg-rose-500"></span>
                    Rejected
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                    <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Pending Review
                </span>
            @endif
        </x-admin.page-header>

        {{-- Rejection alert if rejected --}}
        @if ((int) $data->approval_status === AppUserConst::STATUS_REJECTED)
            <div class="rounded-2xl border border-rose-200 bg-rose-50/80 p-4 dark:border-rose-900/50 dark:bg-rose-950/20 text-rose-900 dark:text-rose-300">
                <div class="flex items-start gap-3">
                    <svg class="size-5 shrink-0 mt-0.5 text-rose-600 dark:text-rose-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="text-sm">
                        <p class="font-semibold">Registration Request was Rejected</p>
                        <p class="mt-1 text-rose-700 dark:text-rose-300">
                            <strong>Reason:</strong> {{ $data->rejection_reason ?: 'No specific reason provided.' }}
                        </p>
                        <p class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                            💡 Notice: Submitting this form with updated details will automatically resubmit the request for review (status will be reset to Pending Approval).
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <x-admin.card>
            <form id="edit-app-user-form" action="{{ route('partner.app_users.doUpdate', $data->id) }}" method="POST" navigate-form class="space-y-8">
                @csrf

                {{-- 1. Personal Information --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        1. Personal Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Full Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $data->name) }}" required
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 @error('name') border-rose-500 @enderror">
                            @error('name')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nickname" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Preferred Nickname
                            </label>
                            <input type="text" id="nickname" name="nickname" value="{{ old('nickname', $data->nickname) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <div>
                            <label for="national_id_number" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                National Identity Number (NIK / KTP)
                            </label>
                            <input type="text" id="national_id_number" name="national_id_number" value="{{ old('national_id_number', $data->national_id_number) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Gender
                            </label>
                            <select id="gender" name="gender"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                                <option value="">Select Gender</option>
                                @foreach ($genders as $val => $label)
                                    <option value="{{ $val }}" {{ old('gender', $data->gender) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Date of Birth
                            </label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $data->date_of_birth) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <div>
                            <label for="phone_number" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Phone Number
                            </label>
                            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $data->phone_number) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Email Address (Optional)
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email', $data->email) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>
                    </div>
                </div>

                {{-- 2. Visual Impairment Details --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        2. Visual Impairment Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="visual_impairment_level" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Impairment Level <span class="text-rose-500">*</span>
                            </label>
                            <select id="visual_impairment_level" name="visual_impairment_level" required
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                                @foreach ($levels as $val => $label)
                                    <option value="{{ $val }}" {{ old('visual_impairment_level', $data->visual_impairment_level) == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="visual_impairment_cause" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Cause of Impairment
                            </label>
                            <input type="text" id="visual_impairment_cause" name="visual_impairment_cause" value="{{ old('visual_impairment_cause', $data->visual_impairment_cause) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>
                    </div>
                </div>

                {{-- 3. Address & Location --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        3. Address
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Street Address
                            </label>
                            <textarea id="address" name="address" rows="2"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">{{ old('address', $data->address) }}</textarea>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                City / Regency
                            </label>
                            <input type="text" id="city" name="city" value="{{ old('city', $data->city) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <div>
                            <label for="province" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Province
                            </label>
                            <input type="text" id="province" name="province" value="{{ old('province', $data->province) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>
                    </div>
                </div>

                {{-- 4. Guardian / Emergency Contact --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        4. Guardian / Emergency Contact
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label for="guardian_name" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Guardian Full Name
                            </label>
                            <input type="text" id="guardian_name" name="guardian_name" value="{{ old('guardian_name', $data->guardian_name) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <div>
                            <label for="guardian_relation" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Relationship
                            </label>
                            <input type="text" id="guardian_relation" name="guardian_relation" value="{{ old('guardian_relation', $data->guardian_relation) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <div>
                            <label for="guardian_phone" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Guardian Phone
                            </label>
                            <input type="text" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $data->guardian_phone) }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>
                    </div>
                </div>

                {{-- 5. Additional Notes --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        5. Additional Observations / Notes
                    </h3>
                    <div class="mt-4">
                        <textarea id="notes" name="notes" rows="3"
                            class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">{{ old('notes', $data->notes) }}</textarea>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                    <x-admin.button href="{{ route('partner.app_users.index') }}" color="outline-secondary">
                        Cancel
                    </x-admin.button>
                    <x-admin.button type="submit" color="primary">
                        Save Changes
                    </x-admin.button>
                </div>
            </form>
        </x-admin.card>
    </div>
@endsection
