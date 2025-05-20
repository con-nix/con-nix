<x-layouts.app :title="__('Repositories')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Header with title and create button -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Repositories</h1>
            <a href="{{ route('repositories.create') }}" class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14m-7-7h14"></path>
                </svg>
                New Repository
            </a>
        </div>
        
        @if($repositories->count() > 0)
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                @foreach($repositories as $repository)
                <div class="flex flex-col rounded-xl border border-neutral-200 p-4 transition-colors hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-800/50">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            <a href="{{ route('repositories.show', $repository) }}" class="text-lg font-medium hover:text-indigo-600 dark:hover:text-indigo-400">
                                {{ $repository->name }}
                            </a>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs {{ $repository->is_public ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                            {{ $repository->is_public ? 'Public' : 'Private' }}
                        </span>
                    </div>
                    
                    @if($repository->description)
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ Str::limit($repository->description, 100) }}
                    </p>
                    @else
                    <p class="text-sm italic text-neutral-500 dark:text-neutral-500">
                        No description provided
                    </p>
                    @endif
                    
                    <div class="mt-auto pt-4 text-xs text-neutral-500 dark:text-neutral-400">
                        <div class="flex items-center justify-between">
                            <span>
                                @if($repository->organization_id)
                                    {{ $repository->organization->name }}
                                @else
                                    Personal
                                @endif
                            </span>
                            <span>Updated {{ $repository->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="my-4">
                {{ $repositories->links() }}
            </div>
        @else
            <div class="flex h-60 flex-col items-center justify-center rounded-xl border border-dashed border-neutral-300 bg-neutral-50 text-center dark:border-neutral-700 dark:bg-neutral-800/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-12 w-12 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white">No repositories yet</h3>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">Get started by creating your first repository</p>
                <a href="{{ route('repositories.create') }}" class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Create Repository
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>