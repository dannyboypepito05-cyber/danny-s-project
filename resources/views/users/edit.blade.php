<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Edit User</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
            @if($errors->any())<div class="mb-6 rounded-md bg-rose-50 p-4 text-sm text-rose-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="name">Name</label><input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="name" name="name" value="{{ old('name', $user->name) }}" required></div>

                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email">Email</label><input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
                <div class="grid gap-6 sm:grid-cols-2"><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="password">New password</label><input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="password" type="password" name="password"><p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password.</p></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="password_confirmation">Confirm password</label><input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="password_confirmation" type="password" name="password_confirmation"></div></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="role">Role</label><select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="role" name="role" required><option value="Administrator" @selected(old('role', $user->role) === 'Administrator')>Administrator</option><option value="Instructor" @selected(old('role', $user->role) === 'Instructor')>Instructor</option><option value="Staff" @selected(old('role', $user->role) === 'Staff')>Staff</option><option value="Student" @selected(old('role', $user->role) === 'Student')>Student</option></select></div>
                <div class="flex gap-3"><button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500" type="submit">Update user</button><a class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300" href="{{ route('users.index') }}">Cancel</a></div>
            </form>
        </div>
    </div></div>
</x-app-layout>
