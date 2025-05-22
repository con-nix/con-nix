<x-layouts.app title="Activity Feed">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Activity Feed</h1>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                    See what people you follow are up to
                </p>
            </div>
            <flux:badge color="blue">{{ $activities->count() }} activities</flux:badge>
        </div>

        <!-- Activity List -->
        <div class="space-y-4">
            @forelse($activities as $activity)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-700 rounded-full flex items-center justify-center text-sm font-medium">
                            {{ substr($activity->user->name, 0, 1) }}
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $activity->user->name }}</span>
                                <span class="text-zinc-500">·</span>
                                <span class="text-xs text-zinc-500">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                            
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $activity->description }}
                            </p>
                        
                            @if($activity->subject)
                                <div class="mt-3">
                                    @if($activity->subject instanceof \App\Models\Repository)
                                        <a href="{{ route('repositories.show', $activity->subject) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                                            📁 {{ $activity->subject->name }}
                                        </a>
                                    @elseif($activity->subject instanceof \App\Models\Organization)
                                        <a href="{{ route('organizations.show', $activity->subject) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                                            👥 {{ $activity->subject->name }}
                                        </a>
                                    @endif
                                </div>
                            @endif
                        
                            <!-- Activity Type Icon -->
                            <div class="mt-3">
                                @switch($activity->type)
                                    @case('repository_created')
                                        <flux:badge color="green" size="sm">
                                            Created Repository
                                        </flux:badge>
                                        @break
                                    @case('repository_updated')
                                        <flux:badge color="blue" size="sm">
                                            Updated Repository
                                        </flux:badge>
                                        @break
                                    @case('organization_created')
                                        <flux:badge color="purple" size="sm">
                                            Created Organization
                                        </flux:badge>
                                        @break
                                    @case('organization_updated')
                                        <flux:badge color="indigo" size="sm">
                                            Updated Organization
                                        </flux:badge>
                                        @break
                                    @default
                                        <flux:badge color="zinc" size="sm">
                                            Activity
                                        </flux:badge>
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="mx-auto w-12 h-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 2 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="mt-4 text-sm font-medium">No activity yet</h3>
                    <p class="mt-2 text-zinc-500">
                        Follow other users to see their activity in your feed.
                    </p>
                    <div class="mt-6">
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            🧭 Explore Users
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>