<x-layouts.app :title="$repository->name">
    <div class="space-y-6">
        <!-- Repository Header -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    <h1 class="text-2xl font-bold">{{ $repository->name }}</h1>
                    <span class="rounded-full px-2 py-1 text-xs {{ $repository->is_public ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                        {{ $repository->is_public ? 'Public' : 'Private' }}
                    </span>
                </div>
                <p class="mt-1 text-neutral-600 dark:text-neutral-400">
                    @if($repository->organization_id)
                        {{ $repository->organization->name }}
                    @else
                        Personal Repository
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                @can('update', $repository)
                    <a href="{{ route('repositories.edit', $repository) }}" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-800">
                        Edit
                    </a>
                @endcan
                @can('delete', $repository)
                    <form action="{{ route('repositories.destroy', $repository) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700 dark:bg-neutral-800 dark:text-red-400 dark:hover:bg-red-900/30 dark:focus:ring-offset-neutral-800" onclick="return confirm('Are you sure you want to delete this repository?')">
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Repository Description -->
        <div class="rounded-md bg-white p-4 shadow-sm dark:bg-neutral-800">
            <h2 class="text-lg font-semibold">About</h2>
            <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                @if($repository->description)
                    {{ $repository->description }}
                @else
                    <span class="italic text-neutral-500">No description provided</span>
                @endif
            </p>
            <div class="mt-4 text-sm text-neutral-500">
                <p>Created: {{ $repository->created_at->format('F j, Y') }}</p>
                <p>Last updated: {{ $repository->updated_at->format('F j, Y') }}</p>
            </div>
        </div>

        <!-- Placeholder for Repository Content -->
        <div class="rounded-md bg-white p-4 shadow-sm dark:bg-neutral-800">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Files</h2>
                <div class="text-sm text-neutral-500">
                    Main Branch
                </div>
            </div>
            <div class="mt-4 flex h-32 items-center justify-center rounded-md border border-dashed border-neutral-300 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900">
                <p class="text-center text-neutral-500">
                    Repository content will be displayed here
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>