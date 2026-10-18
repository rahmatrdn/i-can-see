@extends('_admin._layout.app')

@section('title', 'Register App User (Visual Impairment)')

@php
    use App\Constants\AppUserConst;
@endphp

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <x-admin.page-header :title="'Register App User'" subtitle="Submit a registration request for a visually impaired user" :backUrl="route('admin.app_users.index')" />

        {{-- Information Banner --}}
        <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4 dark:border-blue-900/40 dark:bg-blue-950/20 text-blue-900 dark:text-blue-300 flex items-start gap-3">
            <svg class="size-5 shrink-0 mt-0.5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm leading-relaxed">
                <span class="font-semibold">Registration Review Policy:</span>
                Registrations submitted through this form will initially be marked as <strong class="font-semibold text-amber-700 dark:text-amber-400">Pending Approval</strong>. The application admin will review the submitted details before approving the account for active mobile app usage.
            </div>
        </div>

        <x-admin.card>
            <form id="add-app-user-form" action="{{ route('admin.app_users.create') }}" method="POST" navigate-form class="space-y-8">
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
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400 @error('name') border-rose-500 @enderror"
                                placeholder="e.g. John Doe">
                            @error('name')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nickname" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Preferred Nickname
                            </label>
                            <input type="text" id="nickname" name="nickname" value="{{ old('nickname') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="e.g. Johnny">
                        </div>

                        <div>
                            <label for="national_id_number" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                National Identity Number (NIK / KTP)
                            </label>
                            <input type="text" id="national_id_number" name="national_id_number" value="{{ old('national_id_number') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="16-digit ID number">
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Gender
                            </label>
                            <select id="gender" name="gender"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                                <option value="">Select Gender</option>
                                @foreach ($genders as $val => $label)
                                    <option value="{{ $val }}" {{ old('gender') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Date of Birth
                            </label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <div>
                            <label for="phone_number" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Phone Number
                            </label>
                            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="e.g. 081234567890">
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Username <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400 @error('username') border-rose-500 @enderror"
                                placeholder="e.g. johndoe">
                            @error('username')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Used for mobile app login</p>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Password <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" id="password" name="password" required
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400 @error('password') border-rose-500 @enderror"
                                placeholder="Minimum 6 characters">
                            @error('password')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Re-enter Password <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400 @error('password_confirmation') border-rose-500 @enderror"
                                placeholder="Re-enter password">
                            @error('password_confirmation')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
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
                                    <option value="{{ $val }}" {{ old('visual_impairment_level', 1) == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="visual_impairment_cause" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Cause of Impairment
                            </label>
                            <input type="text" id="visual_impairment_cause" name="visual_impairment_cause" value="{{ old('visual_impairment_cause') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="e.g. Congenital, Glaucoma, Accident">
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
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="Full residential street address">{{ old('address') }}</textarea>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                City / Regency
                            </label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="e.g. Jakarta Selatan">
                        </div>

                        <div>
                            <label for="province" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Province
                            </label>
                            <input type="text" id="province" name="province" value="{{ old('province') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="e.g. DKI Jakarta">
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
                            <input type="text" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="e.g. Mary Doe">
                        </div>

                        <div>
                            <label for="guardian_relation" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Relationship
                            </label>
                            <input type="text" id="guardian_relation" name="guardian_relation" value="{{ old('guardian_relation') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="e.g. Parent, Sibling, Spouse">
                        </div>

                        <div>
                            <label for="guardian_phone" class="block text-sm font-medium mb-1.5 text-neutral-700 dark:text-neutral-300">
                                Guardian Phone
                            </label>
                            <input type="text" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}"
                                class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                                placeholder="e.g. 081298765432">
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
                            class="py-2.5 px-3.5 block w-full border border-neutral-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 placeholder-neutral-400"
                            placeholder="Special assistance requirements, daily routines, or notes for the administration team">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                    <x-admin.button href="{{ route('admin.app_users.index') }}" color="outline-secondary">
                        Cancel
                    </x-admin.button>
                    <x-admin.button type="submit" color="primary">
                        Submit Registration Request
                    </x-admin.button>
                </div>
            </form>
        </x-admin.card>
    </div>
@endsection
