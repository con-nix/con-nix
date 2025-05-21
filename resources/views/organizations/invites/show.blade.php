<x-layouts.app :title="'Organization Invitation'">
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-neutral-800">
            <div class="px-4 py-5 text-center sm:p-6">
                <div class="flex justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-2xl font-semibold leading-6 text-neutral-900 dark:text-neutral-100">
                    You've been invited to join {{ $organization->name }}
                </h3>
                <div class="mt-2 max-w-xl text-center text-sm text-neutral-600 dark:text-neutral-400">
                    <p>
                        You've been invited to join {{ $organization->name }} as a <strong>{{ ucfirst($invite->role) }}</strong>.
                    </p>
                    <p class="mt-1">
                        This invitation was sent by <strong>{{ $invite->sender->name }}</strong> and will expire on {{ $invite->expires_at->format('F j, Y') }}.
                    </p>
                </div>
                
                <div class="mt-8 flex justify-center space-x-4">
                    @auth
                        @if(auth()->user()->email === $invite->email)
                            <form action="{{ route('invites.accept', $invite->token) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Accept Invitation
                                </button>
                            </form>
                            <form action="{{ route('invites.decline', $invite->token) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-800">
                                    Decline
                                </button>
                            </form>
                        @else
                            <div class="rounded-md bg-yellow-50 p-4 dark:bg-yellow-900/30">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Wrong Account</h3>
                                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-200">
                                            <p>
                                                This invitation was sent to {{ $invite->email }}, but you're logged in as {{ auth()->user()->email }}.
                                                Please log out and sign in with the correct account.
                                            </p>
                                            <div class="mt-4">
                                                <form action="{{ route('logout') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-yellow-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:bg-yellow-700 dark:hover:bg-yellow-600">
                                                        Log Out
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center">
                            <p class="mb-4 text-neutral-600 dark:text-neutral-400">
                                You need to log in to accept this invitation. If you don't have an account, you'll need to register.
                            </p>
                            <div class="flex justify-center space-x-4">
                                <a href="{{ route('login') }}" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Login
                                </a>
                                <a href="{{ route('register') }}" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-800">
                                    Register
                                </a>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>