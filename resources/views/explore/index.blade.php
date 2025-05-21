<x-layouts.app title="Explore">
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Explore Repositories</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Discover {{ number_format($totalPublicRepositories) }} public repositories from the community
            </p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg border border-gray-200 p-4 dark:bg-gray-800 dark:border-gray-700">
            <form method="GET" action="{{ route('explore') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Search repositories
                        </label>
                        <input 
                            type="text" 
                            name="search" 
                            id="search"
                            value="{{ $search }}" 
                            placeholder="Search by name or description..." 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        >
                    </div>

                    <!-- Owner Type Filter -->
                    <div>
                        <label for="owner_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Owner type
                        </label>
                        <select 
                            name="owner_type" 
                            id="owner_type"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        >
                            <option value="">All</option>
                            <option value="user" {{ $ownerType === 'user' ? 'selected' : '' }}>Personal</option>
                            <option value="organization" {{ $ownerType === 'organization' ? 'selected' : '' }}>Organization</option>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Sort by
                        </label>
                        <select 
                            name="sort" 
                            id="sort"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        >
                            <option value="latest" {{ $sortBy === 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="updated" {{ $sortBy === 'updated' ? 'selected' : '' }}>Recently updated</option>
                            <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="oldest" {{ $sortBy === 'oldest' ? 'selected' : '' }}>Oldest</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @if($search || $ownerType)
                            Showing {{ $repositories->total() }} result{{ $repositories->total() !== 1 ? 's' : '' }}
                            @if($search)
                                for "<strong>{{ $search }}</strong>"
                            @endif
                        @else
                            Showing all {{ $repositories->total() }} public repositories
                        @endif
                    </div>
                    <div class="flex gap-2">
                        @if($search || $ownerType)
                            <a href="{{ route('explore') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                                Clear filters
                            </a>
                        @endif
                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results -->
        @if($repositories->count() > 0)
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($repositories as $repository)
                <div class="flex flex-col rounded-xl border border-neutral-200 p-4 transition-colors hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-800/50">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            <a href="{{ route('repositories.show', $repository) }}" class="text-lg font-medium hover:text-indigo-600 dark:hover:text-indigo-400" wire:navigate>
                                {{ $repository->name }}
                            </a>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            Public
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <div class="flex items-center space-x-2 text-sm text-neutral-600 dark:text-neutral-400">
                            @if($repository->organization_id && $repository->organization)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>{{ $repository->organization->name }}</span>
                            @elseif($repository->user)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>{{ $repository->user->name }}</span>
                            @else
                                <span class="text-gray-500">Unknown owner</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($repository->description)
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">
                        {{ Str::limit($repository->description, 120) }}
                    </p>
                    @else
                    <p class="text-sm italic text-neutral-500 dark:text-neutral-500 mb-3">
                        No description provided
                    </p>
                    @endif
                    
                    <div class="mt-auto text-xs text-neutral-500 dark:text-neutral-400">
                        <div class="flex items-center justify-between">
                            <span>Updated {{ $repository->updated_at->diffForHumans() }}</span>
                            <span>Created {{ $repository->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($repositories->hasPages())
                <div class="flex justify-center">
                    {{ $repositories->links() }}
                </div>
            @endif
        @else
            <div class="flex h-60 flex-col items-center justify-center rounded-xl border border-dashed border-neutral-300 bg-neutral-50 text-center dark:border-neutral-700 dark:bg-neutral-800/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-12 w-12 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white">No repositories found</h3>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                    @if($search || $ownerType)
                        Try adjusting your search criteria or <a href="{{ route('explore') }}" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">browse all repositories</a>
                    @else
                        There are no public repositories to explore yet.
                    @endif
                </p>
            </div>
        @endif
    </div>
</x-layouts.app>