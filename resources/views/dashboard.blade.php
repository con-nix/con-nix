<x-layouts.app :title="__('Dashboard')">
    <div class="space-y-8">
        <!-- Welcome Header -->
        <div>
            <h1 class="text-2xl font-bold">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                Here's what's happening in your projects and network.
            </p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column: Repositories -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Header with title and create button -->
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Your Repositories</h2>
                    <flux:button :href="route('repositories.create')" variant="primary" icon="plus">
                        New Repository
                    </flux:button>
                </div>
        
                @php
                    $repositories = auth()->user()->allRepositories()->latest()->take(6)->get();
                @endphp
                
                @if($repositories->count() > 0)
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($repositories as $repository)
                        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2z"></path>
                                    </svg>
                                    <a href="{{ route('repositories.show', $repository) }}" class="font-medium hover:text-blue-600">
                                        {{ $repository->name }}
                                    </a>
                                </div>
                                <flux:badge color="{{ $repository->is_public ? 'green' : 'amber' }}" size="sm">
                                    {{ $repository->is_public ? 'Public' : 'Private' }}
                                </flux:badge>
                            </div>
                            
                            @if($repository->description)
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">
                                {{ Str::limit($repository->description, 100) }}
                            </p>
                            @else
                            <p class="text-sm italic text-zinc-500 mb-3">
                                No description provided
                            </p>
                            @endif
                            
                            <div class="flex items-center justify-between text-xs text-zinc-500">
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
                        @endforeach
                    </div>
                    
                    @if(auth()->user()->allRepositories()->count() > 6)
                        <div class="text-center">
                            <flux:button :href="route('repositories.index')" variant="ghost">
                                View all repositories
                            </flux:button>
                        </div>
                    @endif
                @else
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-12 text-center">
                        <svg class="mx-auto w-12 h-12 text-zinc-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-sm font-medium mb-2">No repositories yet</h3>
                        <p class="text-zinc-500 mb-6">Get started by creating your first repository</p>
                        <a href="{{ route('repositories.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            ➕ Create Repository
                        </a>
                    </div>
                @endif
            </div>

            <!-- Right Column: Activity Feed -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Recent Activity</h2>
                    <flux:button :href="route('feed')" variant="ghost" size="sm">
                        View all
                    </flux:button>
                </div>
                
                @php
                    $recentActivities = auth()->user()->getActivityFeed(5);
                @endphp
                
                @if($recentActivities->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentActivities as $activity)
                        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 bg-zinc-200 dark:bg-zinc-700 rounded-full flex items-center justify-center text-xs font-medium">
                                    {{ substr($activity->user->name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1 text-sm">
                                        <span class="font-medium">{{ $activity->user->name }}</span>
                                        <span class="text-zinc-500">{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                                        {{ Str::limit($activity->description, 80) }}
                                    </p>
                                    @if($activity->subject)
                                        <div class="mt-2">
                                            @if($activity->subject instanceof \App\Models\Repository)
                                                <flux:badge color="blue" size="xs">{{ $activity->subject->name }}</flux:badge>
                                            @elseif($activity->subject instanceof \App\Models\Organization)
                                                <flux:badge color="purple" size="xs">{{ $activity->subject->name }}</flux:badge>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-8 text-center">
                        <svg class="mx-auto w-8 h-8 text-zinc-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <h3 class="text-xs font-medium mb-2">No recent activity</h3>
                        <p class="text-xs text-zinc-500 mb-4">Follow users to see their activity here</p>
                        <a href="#" class="text-xs text-blue-600 hover:text-blue-800">
                            Discover Users
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
