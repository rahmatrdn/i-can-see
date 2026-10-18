@extends('_admin._layout.auth')

@section('title', 'Sign In')

@section('content')
    <div class="w-full max-w-md py-6 px-4">
        <div class="mb-6 flex justify-center">
            <img src="{{ asset('images/logo-icansee.png') }}" alt="iCanSee" class="w-auto h-14">
        </div>

        <x-admin.card fit class="w-full">
            <div class="p-2 sm:p-4">
                <div class="text-center mb-6">
                    <h1 class="block text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white">Sign In</h1>
                    <p class="mt-2 text-sm text-gray-400 dark:text-neutral-400">
                        Please sign in with your account credentials
                    </p>
                </div>

                <form id="login-form" action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="grid gap-y-4">
                        @if (session('success'))
                            <div class="bg-teal-50 border border-teal-200 text-sm text-teal-800 rounded-lg p-4 mb-2 dark:bg-teal-800/10 dark:border-teal-900 dark:text-teal-400"
                                role="alert">
                                <span class="font-bold">Success:</span> {{ session('success') }}
                            </div>
                        @endif

                        @error('login_error')
                            <div class="bg-red-50 border border-red-200 text-sm text-red-600 rounded-lg p-4 mb-2 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500"
                                role="alert" tabindex="-1" aria-labelledby="hs-soft-color-danger-label">
                                <span id="hs-soft-color-danger-label" class="font-bold"></span> {{ $message }}
                            </div>
                        @enderror

                        <!-- Form Group -->
                        <div>
                            <label for="email" class="block text-sm mb-2 dark:text-white">Account (Email / ID)</label>
                            <div class="relative">
                                <input type="text" id="email" name="email"
                                    class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                    placeholder="name@example.com"
                                    required aria-describedby="email-error">
                            </div>
                        </div>
                        <!-- End Form Group -->

                        <!-- Form Group -->
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="password" class="block text-sm mb-2 dark:text-white">Password</label>
                            </div>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                    class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                    placeholder="Enter your password"
                                    required aria-describedby="password-error">
                            </div>
                        </div>
                        <!-- End Form Group -->

                        <x-admin.button type="submit" id="login-btn" color="primary" size="lg" class="w-full mt-2">
                            <span id="btn-text">Sign In</span>
                            <span id="btn-spinner"
                                class="animate-spin size-4 border-[3px] border-current border-t-transparent text-white rounded-full hidden"
                                role="status" aria-label="loading">
                                <span class="sr-only">Loading...</span>
                            </span>
                            <span id="btn-loading-text" class="hidden">Signing In...</span>
                        </x-admin.button>

                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-neutral-800 text-center space-y-2">
                            <p class="text-sm text-gray-500 dark:text-neutral-400">
                                Don't have a partner account?
                            </p>
                            <x-admin.button :href="route('register')" color="outline-secondary" size="md" class="w-full">
                                Register as a Partner
                            </x-admin.button>
                        </div>
                    </div>
                </form>
            </div>
        </x-admin.card>
    </div>

    @push('scripts')
        <script>
            document.getElementById('login-form').addEventListener('submit', function() {
                const btn = document.getElementById('login-btn');
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
