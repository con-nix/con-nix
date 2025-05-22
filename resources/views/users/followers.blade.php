<x-layouts.app :title="$user->name . ' - Followers'">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-zinc-200 dark:bg-zinc-700 rounded-full flex items-center justify-center text-xl font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                        <p class="text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                @if(auth()->id() !== $user->id)
                    @if(auth()->user()->isFollowing($user))
                        <form action="{{ route('users.unfollow', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <flux:button type="submit" variant="ghost" icon="user-minus">
                                Unfollow
                            </flux:button>
                        </form>
                    @else
                        <form action="{{ route('users.follow', $user) }}" method="POST" class="inline">
                            @csrf
                            <flux:button type="submit" variant="primary" icon="user-plus">
                                Follow
                            </flux:button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        <!-- Stats -->
        <div class="flex gap-6">
            <flux:button :href="route('users.followers', $user)" variant="ghost" class="font-normal">
                <flux:badge color="blue">{{ $user->followers_count }}</flux:badge>
                <span class="ml-2">followers</span>
            </flux:button>
            <flux:button :href="route('users.following', $user)" variant="ghost" class="font-normal">
                <flux:badge color="green">{{ $user->following_count }}</flux:badge>
                <span class="ml-2">following</span>
            </flux:button>
        </div>

        <!-- Followers List -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold">Followers</h2>
                <flux:badge color="blue">{{ $followers->total() }}</flux:badge>
            </div>
            
            <div class="space-y-3">
                @forelse($followers as $follower)
                    <div class="flex items-center justify-between p-3 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-700 rounded-full flex items-center justify-center text-sm font-medium">
                                {{ substr($follower->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-medium">{{ $follower->name }}</div>
                                <div class="text-sm text-zinc-500">{{ $follower->email }}</div>
                            </div>
                        </div>
                        
                        @if(auth()->id() !== $follower->id)
                            <div>
                                @if(auth()->user()->isFollowing($follower))
                                    <form action="{{ route('users.unfollow', $follower) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" variant="ghost" size="xs" class="text-zinc-600 hover:text-zinc-900">
                                            Unfollow
                                        </flux:button>
                                    </form>
                                @else
                                    <form action="{{ route('users.follow', $follower) }}" method="POST" class="inline">
                                        @csrf
                                        <flux:button type="submit" variant="ghost" size="xs" class="text-blue-600 hover:text-blue-700">
                                            Follow
                                        </flux:button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto w-12 h-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <h3 class="text-sm font-medium mt-4">No followers yet</h3>
                        <p class="text-zinc-500 mt-2">
                            {{ $user->name }} doesn't have any followers yet.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($followers->hasPages())
            <div class="mt-6">
                {{ $followers->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>