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
                        <button type="submit" class="inline-flex items-center rounded-md border border-indigo-500 bg-indigo-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Mark All as Read
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="border-b border-neutral-200 dark:border-neutral-700">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $filter === 'all' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    All
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $filter === 'unread' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    Unread
                    @if(auth()->user()->unread_notifications_count > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                            {{ auth()->user()->unread_notifications_count }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $filter === 'read' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    Read
                </a>
            </nav>
        </div>

        <!-- Notifications List -->
        <div class="space-y-2">
            @forelse($notifications as $notification)
                <div class="flex items-start p-4 rounded-lg border {{ $notification->isRead() ? 'bg-white dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700' : 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700' }}">
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
                            
                            <div class="ml-2 flex items-center space-x-2">
                                @if(!$notification->isRead())
                                    <form action="{{ route('notifications.read', $notification) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Mark as read
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('notifications.unread', $notification) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-300">
                                            Mark as unread
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this notification?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        @if($notification->action_url)
                            <div class="mt-2">
                                <a href="{{ route('notifications.read', $notification) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:text-indigo-300 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50">
                                    View
                                </a>
                            </div>
                        @endif
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