<x-layouts.app title="Activity Feed">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Activity Feed</h1>
                <p class="mt-1 text-neutral-600 dark:text-neutral-400">
                    See what people you follow are up to
                </p>
            </div>
        </div>

        <!-- Activity List -->
        <div class="space-y-4">
            @forelse($activities as $activity)
                <div class="flex items-start p-4 bg-white rounded-lg border border-neutral-200 dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 flex-shrink-0 rounded-full bg-neutral-100 flex items-center justify-center dark:bg-neutral-700">
                            <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-300">{{ $activity->user->initials() }}</span>
                        </div>
                    </div>
                    
                    <div class="ml-3 flex-1 min-w-0">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-neutral-900 dark:text-neutral-200">
                                {{ $activity->user->name }}
                            </span>
                            <span class="mx-2 text-neutral-500 dark:text-neutral-400">·</span>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $activity->created_at->diffForHumans() }}
                            </span>
                        </div>
                        
                        <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                            {{ $activity->description }}
                        </p>
                        
                        @if($activity->subject)
                            <div class="mt-2">
                                @if($activity->subject instanceof \App\Models\Repository)
                                    <a href="{{ route('repositories.show', $activity->subject) }}" class="inline-flex items-center px-2 py-1 border border-neutral-300 rounded text-xs text-neutral-700 bg-neutral-50 hover:bg-neutral-100 dark:border-neutral-600 dark:text-neutral-300 dark:bg-neutral-700 dark:hover:bg-neutral-600">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $activity->subject->name }}
                                    </a>
                                @elseif($activity->subject instanceof \App\Models\Organization)
                                    <a href="{{ route('organizations.show', $activity->subject) }}" class="inline-flex items-center px-2 py-1 border border-neutral-300 rounded text-xs text-neutral-700 bg-neutral-50 hover:bg-neutral-100 dark:border-neutral-600 dark:text-neutral-300 dark:bg-neutral-700 dark:hover:bg-neutral-600">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                        </svg>
                                        {{ $activity->subject->name }}
                                    </a>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Activity Type Icon -->
                        <div class="mt-2 flex items-center">
                            @switch($activity->type)
                                @case('repository_created')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Created Repository
                                    </span>
                                    @break
                                @case('repository_updated')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                        Updated Repository
                                    </span>
                                    @break
                                @case('organization_created')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Created Organization
                                    </span>
                                    @break
                                @case('organization_updated')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                        Updated Organization
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-300">
                                        Activity
                                    </span>
                            @endswitch
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h.01M17 7h.01"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-200">No activity yet</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Follow other users to see their activity in your feed.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('explore') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Explore Users
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>