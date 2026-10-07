<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Add User</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
            @if($errors->any())<div class="mb-6 rounded-md bg-rose-50 p-4 text-sm text-rose-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
                @csrf
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="name">Name</label><input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="name" name="name" value="{{ old('name') }}" required></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email">Email</label><input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="email" type="email" name="email" value="{{ old('email') }}" required></div>
                <div class="grid gap-6 sm:grid-cols-2"><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="password">Password</label><input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="password" type="password" name="password" required></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="password_confirmation">Confirm password</label><input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="password_confirmation" type="password" name="password_confirmation" required></div></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="role">Role</label><select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" id="role" name="role" required><option value="">Select role</option><option value="Administrator">Administrator</option><option value="Instructor">Instructor</option><option value="Staff">Staff</option><option value="Student">Student</option></select></div>
                <div class="flex gap-3"><button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500" type="submit">Create user</button><a class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300" href="{{ route('users.index') }}">Cancel</a></div>
            </form>
        </div>
    </div></div>
</x-app-layout>
