@extends('_admin._layout.auth')

@section('title', 'Partner Registration')

@section('content')
    <div class="w-full max-w-2xl py-10 px-4 sm:px-6">
        <div class="mb-6 flex justify-center">
            <img src="{{ asset('images/logo-icansee.png') }}" alt="iCanSee" class="w-auto h-14">
        </div>

        <x-admin.card fit class="w-full">
            <div class="p-2 sm:p-4">
                <div class="text-center mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white">Partner Registration</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-neutral-400">
                        Register your foundation, community, or organization to register and manage iCanSee app users
                    </p>
                </div>

                @if (session('error'))
                    <div class="bg-red-50 border border-red-200 text-sm text-red-600 rounded-xl p-4 mb-6 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400"
                        role="alert">
                        <span class="font-bold">Error:</span> {{ session('error') }}
                    </div>
                @endif

                <form id="register-form" action="{{ route('register.post') }}" method="POST">
                    @csrf

                    {{-- Section 1: PIC Account Information --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 pb-2 mb-4 border-b border-gray-100 dark:border-neutral-800">
                            <span class="flex items-center justify-center size-6 rounded-full bg-blue-100 text-blue-600 text-xs font-bold dark:bg-blue-900/40 dark:text-blue-400">1</span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-neutral-300">
                                Login Account Information (Person In Charge / PIC)
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label for="name" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    PIC Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="e.g. John Doe" required>
                                @error('name')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="email" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="name@example.com" required>
                                @error('email')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" id="password" name="password" maxlength="8"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="Maximum 8 characters" required>
                                @error('password')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" id="password_confirmation" name="password_confirmation" maxlength="8"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="Repeat your password" required>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Organization / Partner Information --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 pb-2 mb-4 border-b border-gray-100 dark:border-neutral-800">
                            <span class="flex items-center justify-center size-6 rounded-full bg-blue-100 text-blue-600 text-xs font-bold dark:bg-blue-900/40 dark:text-blue-400">2</span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-neutral-300">
                                Organization / Partner Information
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="partner_name" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    Organization / Partner Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="partner_name" name="partner_name" value="{{ old('partner_name') }}"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="Foundation or community name" required>
                                @error('partner_name')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="partner_type" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    Partner Type <span class="text-red-500">*</span>
                                </label>
                                <select id="partner_type" name="partner_type"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300"
                                    required>
                                    <option value="">-- Select Partner Type --</option>
                                    @foreach ($partnerTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('partner_type') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('partner_type')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="pic_phone" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    Phone / WhatsApp Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="pic_phone" name="pic_phone" value="{{ old('pic_phone') }}"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="e.g. +6281234567890" required>
                                @error('pic_phone')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="city" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    City / Regency
                                </label>
                                <input type="text" id="city" name="city" value="{{ old('city') }}"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="e.g. South Jakarta">
                            </div>

                            <div>
                                <label for="province" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    Province / State
                                </label>
                                <input type="text" id="province" name="province" value="{{ old('province') }}"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="e.g. Jakarta">
                            </div>

                            <div>
                                <label for="postal_code" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    Postal Code
                                </label>
                                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="e.g. 12345">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="address" class="block text-sm font-medium mb-1.5 dark:text-white">
                                    Full Address
                                </label>
                                <textarea id="address" name="address" rows="2"
                                    class="py-2.5 px-3.5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500"
                                    placeholder="Office or secretariat street address">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-8 space-y-3">
                        <x-admin.button type="submit" id="register-btn" color="primary" size="lg" class="w-full">
                            <span id="btn-text">Register Now</span>
                            <span id="btn-spinner"
                                class="animate-spin size-4 border-[3px] border-current border-t-transparent text-white rounded-full hidden"
                                role="status" aria-label="loading">
                                <span class="sr-only">Loading...</span>
                            </span>
                            <span id="btn-loading-text" class="hidden">Processing Registration...</span>
                        </x-admin.button>

                        <div class="pt-2 text-center">
                            <p class="text-sm text-gray-500 dark:text-neutral-400 mb-2">
                                Already have an account?
                            </p>
                            <x-admin.button :href="route('login')" color="outline-secondary" size="md" class="w-full">
                                Sign In with Existing Account
                            </x-admin.button>
                        </div>
                    </div>
                </form>
            </div>
        </x-admin.card>
    </div>

    @push('scripts')
        <script>
            document.getElementById('register-form').addEventListener('submit', function() {
                const btn = document.getElementById('register-btn');
                const btnText = document.getElementById('btn-text');
                const btnSpinner = document.getElementById('btn-spinner');
                const btnLoadingText = document.getElementById('btn-loading-text');

                btn.disabled = true;
                btn.classList.add('opacity-60', 'cursor-not-allowed');
                btnText.classList.add('hidden');
                btnSpinner.classList.remove('hidden');
                btnSpinner.classList.add('inline-block');
                btnLoadingText.classList.remove('hidden');
            });
        </script>
    @endpush
@endsection
