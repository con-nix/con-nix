<x-layouts.app :title="$organization->name . ' - Members'">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h1 class="text-2xl font-bold">{{ $organization->name }} - Members</h1>
                </div>
                <p class="mt-1 text-neutral-600 dark:text-neutral-400">
                    Manage members and roles
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('organizations.show', $organization) }}" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-800">
                    Back to Organization
                </a>
                @can('inviteMembers', $organization)
                    <a href="{{ route('organizations.invites.create', $organization) }}" class="inline-flex items-center rounded-md border border-indigo-500 bg-indigo-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:hover:bg-indigo-600 dark:focus:ring-offset-neutral-800">
                        Invite Member
                    </a>
                @endcan
            </div>
        </div>

        <!-- Members List -->
        <div class="rounded-md bg-white p-4 shadow-sm dark:bg-neutral-800">
            <h2 class="text-lg font-semibold">Members</h2>
            
            <div class="mt-4 overflow-hidden rounded-md border border-neutral-200 dark:border-neutral-700">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                Joined
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                        <!-- Organization Owner -->
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 flex-shrink-0 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-xs font-semibold text-indigo-600">{{ $organization->owner->initials() }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-neutral-200">
                                            {{ $organization->owner->name }}
                                        </div>
                                        <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ $organization->owner->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                                    Owner
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $organization->created_at->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <!-- No actions for owner -->
                            </td>
                        </tr>

                        <!-- Members -->
                        @foreach($members as $member)
                            @if($member->user_id != $organization->owner_id)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 flex-shrink-0 rounded-full bg-neutral-100 flex items-center justify-center">
                                                <span class="text-xs font-semibold text-neutral-600">{{ $member->user->initials() }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-neutral-900 dark:text-neutral-200">
                                                    {{ $member->user->name }}
                                                </div>
                                                <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                                    {{ $member->user->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        @can('manageMembers', $organization)
                                            <form action="{{ route('organizations.members.update', [$organization, $member]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" onchange="this.form.submit()" class="rounded-md border-neutral-300 text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                                    <option value="admin" {{ $member->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="member" {{ $member->role === 'member' ? 'selected' : '' }}>Member</option>
                                                    <option value="viewer" {{ $member->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                                </select>
                                            </form>
                                        @else
                                            <span class="inline-flex rounded-full {{ $member->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : ($member->role === 'member' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400') }} px-2 py-1 text-xs font-semibold">
                                                {{ ucfirst($member->role) }}
                                            </span>
                                        @endcan
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ $member->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        @if(auth()->id() === $member->user_id || auth()->user()->can('manageMembers', $organization))
                                            <form action="{{ route('organizations.members.destroy', [$organization, $member]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to remove this member?')">
                                                    {{ auth()->id() === $member->user_id ? 'Leave' : 'Remove' }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Invites -->
        @can('inviteMembers', $organization)
            @if(count($invites) > 0)
                <div class="rounded-md bg-white p-4 shadow-sm dark:bg-neutral-800">
                    <h2 class="text-lg font-semibold">Pending Invites</h2>
                    
                    <div class="mt-4 overflow-hidden rounded-md border border-neutral-200 dark:border-neutral-700">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Email
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Role
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Sent By
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Expires
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                                @foreach($invites as $invite)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-200">
                                            {{ $invite->email }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span class="inline-flex rounded-full {{ $invite->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : ($invite->role === 'member' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400') }} px-2 py-1 text-xs font-semibold">
                                                {{ ucfirst($invite->role) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ $invite->sender->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ $invite->expires_at->format('M d, Y') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <form action="{{ route('organizations.invites.cancel', [$organization, $invite]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to cancel this invitation?')">
                                                    Cancel
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endcan
    </div>
</x-layouts.app>