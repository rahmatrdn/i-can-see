@extends('_admin._layout.app')

@section('title', 'Ubah Password')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
            class="bg-white overflow-hidden shadow-lg rounded-2xl dark:bg-neutral-800 border-2 border-gray-100 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                        Ubah Password
                    </h2>
                </div>
            </div>

            <form id="change-password-form" class="p-6" navigate-form
                action="{{ route('admin.profile.do_change_password') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    {{-- Current Password --}}
                    <div>
                        <label for="current_password" class="block text-sm font-medium mb-2 dark:text-white">Password Lama
                            <span class="text-red-500">*</span></label>
                        <input type="password" id="current_password" name="current_password"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 placeholder-neutral-300 dark:placeholder-neutral-500 dark:focus:ring-neutral-600 @error('current_password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                            placeholder="Masukkan password lama anda" required>
                        @error('current_password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium mb-2 dark:text-white">Password Baru <span
                                class="text-red-500">*</span></label>
                        <input type="password" id="password" name="password"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 placeholder-neutral-300 dark:placeholder-neutral-500 dark:focus:ring-neutral-600 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                            placeholder="Masukkan password baru anda" required>
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm New Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium mb-2 dark:text-white">Ulangi
                            Password Baru
                            <span class="text-red-500">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 placeholder-neutral-300 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                            placeholder="Ulangi password baru anda" required>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="mt-4 flex justify-start gap-x-2">
                    <button type="submit"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none cursor-pointer">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
