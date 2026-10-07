<x-guest-layout>
    <div class="bg-slate-950/40 p-6 sm:p-8">
        <div class="mb-6 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-indigo-300">Secure Access</p>
            <h1 class="mt-3 text-3xl font-bold text-white">Welcome back to DUCQUE</h1>
            <p class="mt-2 text-sm text-slate-300">Manage classrooms, instructors, and academic operations with confidence.</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" class="text-slate-200" />
                <x-text-input id="email" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900/80 text-white placeholder:text-slate-400 focus:border-indigo-400 focus:ring-indigo-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" class="text-slate-200" />
                <x-text-input id="password" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900/80 text-white placeholder:text-slate-400 focus:border-indigo-400 focus:ring-indigo-500" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="role" :value="__('Role')" class="text-slate-200" />
                <select id="role" name="role" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900/80 text-white focus:border-indigo-400 focus:ring-indigo-500" required>
                    <option value="">Select your role</option>
                    <option value="Administrator" @selected(old('role') === 'Administrator')>Administrator</option>
                    <option value="Staff" @selected(old('role') === 'Staff')>Staff</option>
                    <option value="Student" @selected(old('role') === 'Student')>Student</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-600 bg-slate-900 text-indigo-500 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-slate-300">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-indigo-300 underline-offset-4 hover:text-indigo-200 hover:underline" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div class="pt-2">
                <x-primary-button class="w-full justify-center rounded-xl bg-indigo-500 px-5 py-3 text-base font-semibold shadow-lg shadow-indigo-500/30 hover:bg-indigo-400">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            Need access? <a href="{{ route('register') }}" class="font-medium text-indigo-300 hover:text-indigo-200">Create an account</a>
        </p>
    </div>
</x-guest-layout>
