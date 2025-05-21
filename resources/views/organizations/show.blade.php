<x-layouts.app :title="$organization->name">
    <div class="space-y-6">
        <!-- Organization Header -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h1 class="text-2xl font-bold">{{ $organization->name }}</h1>
                </div>
                <p class="mt-1 text-neutral-600 dark:text-neutral-400">
                    Owner: {{ $organization->owner->name }}
                </p>
            </div>
            <div class="flex gap-2">
                @can('update', $organization)
                    <a href="{{ route('organizations.edit', $organization) }}" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-800">
                        Edit
                    </a>
                @endcan
                @can('delete', $organization)
                    <form action="{{ route('organizations.destroy', $organization) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700 dark:bg-neutral-800 dark:text-red-400 dark:hover:bg-red-900/30 dark:focus:ring-offset-neutral-800" onclick="return confirm('Are you sure you want to delete this organization?')">
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Organization Description -->
        <div class="rounded-md bg-white p-4 shadow-sm dark:bg-neutral-800">
            <h2 class="text-lg font-semibold">About</h2>
            <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                @if($organization->description)
                    {{ $organization->description }}
                @else
                    <span class="italic text-neutral-500">No description provided</span>
                @endif
            </p>
            <div class="mt-4 text-sm text-neutral-500">
                <p>Created: {{ $organization->created_at->format('F j, Y') }}</p>
                <p>Last updated: {{ $organization->updated_at->format('F j, Y') }}</p>
            </div>
        </div>

        <!-- Organization Repositories -->
        <div class="rounded-md bg-white p-4 shadow-sm dark:bg-neutral-800">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Repositories</h2>
                <a href="{{ route('repositories.create') }}" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-800">
                    New Repository
                </a>
            </div>
            
            @if($organization->repositories->count() > 0)
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach($organization->repositories as $repository)
                        <div class="rounded-md border border-neutral-200 p-3 dark:border-neutral-700">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('repositories.show', $repository) }}" class="font-medium hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $repository->name }}
                                </a>
                                <span class="rounded-full px-2 py-1 text-xs {{ $repository->is_public ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                    {{ $repository->is_public ? 'Public' : 'Private' }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                {{ Str::limit($repository->description ?? 'No description', 50) }}
                            </p>
                            <p class="mt-2 text-xs text-neutral-500">
                                Updated {{ $repository->updated_at->diffForHumans() }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-4 flex h-24 items-center justify-center rounded-md border border-dashed border-neutral-300 bg-neutral-50 text-center dark:border-neutral-700 dark:bg-neutral-900">
                    <p class="text-neutral-500">
                        No repositories in this organization yet
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>