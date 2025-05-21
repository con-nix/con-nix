<x-layouts.app :title="__('Create Organization')">
    <div class="mb-8">
        <h1 class="text-2xl font-bold">Create Organization</h1>
        <p class="mt-1 text-neutral-600 dark:text-neutral-400">Create a new organization to collaborate with others</p>
    </div>

    <div class="mx-auto max-w-3xl">
        <form action="{{ route('organizations.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Organization Name -->
            <div>
                <x-input-label for="name" :value="__('Organization Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Organization Description -->
            <div>
                <x-input-label for="description" :value="__('Description (Optional)')" />
                <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-indigo-600 dark:focus:ring-indigo-600">{{ old('description') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('organizations.index') }}" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-neutral-700 shadow-sm transition duration-150 ease-in-out hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-800">
                    Cancel
                </a>

                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-700 dark:focus:ring-offset-neutral-800">
                    Create Organization
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>