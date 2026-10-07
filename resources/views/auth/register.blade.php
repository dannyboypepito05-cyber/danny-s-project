<x-guest-layout>
    <div class="bg-slate-950/40 p-6 sm:p-8">
        <div class="mb-6 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-indigo-300">Create Account</p>
            <h1 class="mt-3 text-3xl font-bold text-white">Join DUCQUE</h1>
            <p class="mt-2 text-sm text-slate-300">Establish your student profile and access your academic workspace.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="name" :value="__('Name')" class="text-slate-200" />
                <x-text-input id="name" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900/80 text-white placeholder:text-slate-400 focus:border-indigo-400 focus:ring-indigo-500" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" class="text-slate-200" />
                <x-text-input id="email" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900/80 text-white placeholder:text-slate-400 focus:border-indigo-400 focus:ring-indigo-500" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" class="text-slate-200" />
                <x-text-input id="password" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900/80 text-white placeholder:text-slate-400 focus:border-indigo-400 focus:ring-indigo-500" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-slate-200" />
                <x-text-input id="password_confirmation" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900/80 text-white placeholder:text-slate-400 focus:border-indigo-400 focus:ring-indigo-500" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="pt-2">
                <x-primary-button class="w-full justify-center rounded-xl bg-indigo-500 px-5 py-3 text-base font-semibold shadow-lg shadow-indigo-500/30 hover:bg-indigo-400">
                    {{ __('Register') }}
                </x-primary-button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            Already registered? <a href="{{ route('login') }}" class="font-medium text-indigo-300 hover:text-indigo-200">Sign in</a>
        </p>
    </div>
</x-guest-layout>
