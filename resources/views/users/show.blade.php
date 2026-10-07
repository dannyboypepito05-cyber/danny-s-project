<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">User Details</h2>
            <a href="{{ route('users.index') }}" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Back to users</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Registered {{ $user->created_at->format('M d, Y') }}</p>
                </div>
                <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3"><dt class="text-sm font-medium text-gray-500">Email</dt><dd class="text-sm text-gray-900 sm:col-span-2 dark:text-gray-100">{{ $user->email }}</dd></div>
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3"><dt class="text-sm font-medium text-gray-500">Role</dt><dd class="text-sm text-gray-900 sm:col-span-2 dark:text-gray-100">{{ $user->role }}</dd></div>
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3"><dt class="text-sm font-medium text-gray-500">Last updated</dt><dd class="text-sm text-gray-900 sm:col-span-2 dark:text-gray-100">{{ $user->updated_at->format('M d, Y g:i A') }}</dd></div>
                </dl>
                <div class="flex gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <a href="{{ route('users.edit', $user) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Edit user</a>
                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500" type="submit">Delete user</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
