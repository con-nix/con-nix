<x-layouts.app :title="__('Edit Repository')">
    <div class="mb-8">
        <h1 class="text-2xl font-bold">Edit Repository</h1>
        <p class="mt-1 text-neutral-600 dark:text-neutral-400">Update repository information</p>
    </div>

    <div class="mx-auto max-w-3xl">
        <form action="{{ route('repositories.update', $repository) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Repository Name -->
            <div>
                <x-input-label for="name" :value="__('Repository Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $repository->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Repository Description -->
            <div>
                <x-input-label for="description" :value="__('Description (Optional)')" />
                <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-indigo-600 dark:focus:ring-indigo-600">{{ old('description', $repository->description) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>

            <!-- Visibility Settings -->
            <div>
                <x-input-label :value="__('Visibility')" />
                <div class="mt-2 space-y-3">
                    <div class="flex items-start">
                        <div class="flex h-5 items-center">
                            <input id="is_public" name="is_public" type="checkbox" value="1" class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-600 dark:border-neutral-700 dark:bg-neutral-900 dark:ring-offset-neutral-900 dark:focus:ring-indigo-600" {{ old('is_public', $repository->is_public) == '1' ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3">
                            <label for="is_public" class="font-medium text-neutral-900 dark:text-white">Public</label>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Anyone can see this repository. You choose who can commit.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Owner Information (Read-only) -->
            <div>
                <x-input-label :value="__('Owner')" />
                <div class="mt-2 rounded-md bg-neutral-50 p-3 dark:bg-neutral-800">
                    @if($repository->organization_id)
                        <div class="flex items-center gap-2">
                            <span class="text-neutral-900 dark:text-white">{{ $repository->organization->name }}</span>
                            <span class="text-sm text-neutral-500">(Organization)</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="text-neutral-900 dark:text-white">{{ $repository->user->name }}</span>
                            <span class="text-sm text-neutral-500">(Personal)</span>
                        </div>
                    @endif
                    <p class="mt-1 text-xs text-neutral-600 dark:text-neutral-400">Ownership cannot be changed after creation</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('repositories.show', $repository) }}" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-neutral-700 shadow-sm transition duration-150 ease-in-out hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-800">
                    Cancel
                </a>

                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-700 dark:focus:ring-offset-neutral-800">
                    Update Repository
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>