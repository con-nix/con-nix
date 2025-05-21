<x-layouts.app :title="$user->name . ' - Following'">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 flex-shrink-0 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-lg font-semibold text-indigo-600">{{ $user->initials() }}</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                        <p class="text-neutral-600 dark:text-neutral-400">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                @if(auth()->id() !== $user->id)
                    @if(auth()->user()->isFollowing($user))
                        <form action="{{ route('users.unfollow', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700">
                                Unfollow
                            </button>
                        </form>
                    @else
                        <form action="{{ route('users.follow', $user) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-md border border-indigo-500 bg-indigo-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Follow
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        <!-- Stats -->
        <div class="flex gap-6 text-sm">
            <a href="{{ route('users.followers', $user) }}" class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200">
                <span class="font-semibold">{{ $user->followers_count }}</span> followers
            </a>
            <a href="{{ route('users.following', $user) }}" class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200">
                <span class="font-semibold">{{ $user->following_count }}</span> following
            </a>
        </div>

        <!-- Following List -->
        <div class="rounded-md bg-white p-4 shadow-sm dark:bg-neutral-800">
            <h2 class="text-lg font-semibold mb-4">Following</h2>
            
            @forelse($following as $followedUser)
                <div class="flex items-center justify-between py-3 border-b border-neutral-200 dark:border-neutral-700 last:border-b-0">
                    <div class="flex items-center">
                        <div class="h-8 w-8 flex-shrink-0 rounded-full bg-neutral-100 flex items-center justify-center dark:bg-neutral-700">
                            <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-300">{{ $followedUser->initials() }}</span>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-neutral-900 dark:text-neutral-200">
                                {{ $followedUser->name }}
                            </div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $followedUser->email }}
                            </div>
                        </div>
                    </div>
                    
                    @if(auth()->id() !== $followedUser->id)
                        <div>
                            @if(auth()->user()->isFollowing($followedUser))
                                <form action="{{ route('users.unfollow', $followedUser) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-300">
                                        Unfollow
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('users.follow', $followedUser) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Follow
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-neutral-500 dark:text-neutral-400">
                        {{ $user->name }} isn't following anyone yet.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($following->hasPages())
            <div class="mt-6">
                {{ $following->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>