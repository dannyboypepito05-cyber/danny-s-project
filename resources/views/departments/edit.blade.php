<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">Edit Department</h2>
            <a href="{{ route('departments.index') }}" class="text-sm font-medium text-slate-300 hover:text-white">Back to departments</a>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-3xl rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl shadow-slate-950/30">
            <form method="POST" action="{{ route('departments.update', $department) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-200">Department Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $department->name) }}" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                    @error('name')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-slate-200">Department Code</label>
                    <input id="code" name="code" type="text" value="{{ old('code', $department->code) }}" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                    @error('code')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-slate-200">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none">
                        <option value="Active" {{ old('status', $department->status) === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status', $department->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-200">Description</label>
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none">{{ old('description', $department->description) }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('departments.index') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800">Cancel</a>
                    <button type="submit" class="rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
