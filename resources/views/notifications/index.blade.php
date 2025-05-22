<x-layouts.app title="Notifications">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Notifications</h1>
                <p class="mt-1 text-neutral-600 dark:text-neutral-400">
                    Stay updated with your activity
                </p>
            </div>
            <div class="flex gap-2">
                @if(auth()->user()->unread_notifications_count > 0)
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                        @csrf
                        <flux:button type="submit" variant="primary" size="sm">
                            Mark All as Read
                        </flux:button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $filter === 'all' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300' }}">
                    All
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $filter === 'unread' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300' }} flex items-center gap-2">
                    Unread
                    @if(auth()->user()->unread_notifications_count > 0)
                        <flux:badge color="red" size="sm">
                            {{ auth()->user()->unread_notifications_count }}
                        </flux:badge>
                    @endif
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $filter === 'read' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300' }}">
                    Read
                </a>
            </nav>
        </div>

        <!-- Notifications List -->
        <div class="space-y-3">
            @forelse($notifications as $notification)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 {{ $notification->isRead() ? '' : 'ring-2 ring-blue-500/20 bg-blue-50/50 dark:bg-blue-900/10' }}">
                    <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        @switch($notification->type)
                            @case('user_follow')
                                <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-full dark:bg-green-900/30">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                @break
                            @case('organization_invite')
                                <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full dark:bg-blue-900/30">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                @break
                            @default
                                <div class="flex items-center justify-center w-8 h-8 bg-neutral-100 rounded-full dark:bg-neutral-900/30">
                                    <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-12h0z"></path>
                                    </svg>
                                </div>
                        @endswitch
                    </div>
                    
                    <div class="ml-3 flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-neutral-200">
                                    {{ $notification->title }}
                                </p>
                                @if($notification->message)
                                    <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                        {{ $notification->message }}
                                    </p>
                                @endif
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-500">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            
                            <div class="ml-2 flex items-center gap-2">
                                @if(!$notification->isRead())
                                    <form action="{{ route('notifications.read', $notification) }}" method="POST" class="inline">
                                        @csrf
                                        <flux:button type="submit" variant="ghost" size="xs">
                                            Mark as read
                                        </flux:button>
                                    </form>
                                @else
                                    <form action="{{ route('notifications.unread', $notification) }}" method="POST" class="inline">
                                        @csrf
                                        <flux:button type="submit" variant="ghost" size="xs">
                                            Mark as unread
                                        </flux:button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" variant="ghost" size="xs" class="text-red-600 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this notification?')">
                                        Delete
                                    </flux:button>
                                </form>
                            </div>
                        </div>
                        
                        @if($notification->action_url)
                            <div class="mt-3">
                                <flux:button :href="route('notifications.read', $notification)" variant="filled" size="xs">
                                    View
                                </flux:button>
                            </div>
                        @endif
                    </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-12h0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-200">No notifications</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        @if($filter === 'unread')
                            You have no unread notifications.
                        @elseif($filter === 'read')
                            You have no read notifications.
                        @else
                            You don't have any notifications yet.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->appends(['filter' => $filter])->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>