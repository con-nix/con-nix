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
                <flux:button :href="route('organizations.show', $organization)" variant="ghost">
                    Back to Organization
                </flux:button>
                @can('inviteMembers', $organization)
                    <flux:button :href="route('organizations.invites.create', $organization)" variant="primary" icon="plus">
                        Invite Member
                    </flux:button>
                @endcan
            </div>
        </div>

        <!-- Members List -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold">Members</h2>
                <flux:badge color="blue">{{ count($members) + 1 }} members</flux:badge>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        <!-- Organization Owner -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-700 rounded-full flex items-center justify-center text-sm font-medium">
                                        {{ substr($organization->owner->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $organization->owner->name }}</div>
                                        <div class="text-sm text-zinc-500">{{ $organization->owner->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:badge color="indigo" size="sm">Owner</flux:badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-500">{{ $organization->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <!-- No actions for owner -->
                            </td>
                        </tr>

                        <!-- Members -->
                        @foreach($members as $member)
                            @if($member->user_id != $organization->owner_id)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-700 rounded-full flex items-center justify-center text-sm font-medium">
                                                {{ substr($member->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $member->user->name }}</div>
                                                <div class="text-sm text-zinc-500">{{ $member->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @can('manageMembers', $organization)
                                            <form action="{{ route('organizations.members.update', [$organization, $member]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <flux:select name="role" onchange="this.form.submit()" size="sm">
                                                    <option value="admin" {{ $member->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="member" {{ $member->role === 'member' ? 'selected' : '' }}>Member</option>
                                                    <option value="viewer" {{ $member->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                                </flux:select>
                                            </form>
                                        @else
                                            @php
                                                $badgeColor = $member->role === 'admin' ? 'purple' : ($member->role === 'member' ? 'green' : 'blue');
                                            @endphp
                                            <flux:badge color="{{ $badgeColor }}" size="sm">{{ ucfirst($member->role) }}</flux:badge>
                                        @endcan
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-zinc-500">{{ $member->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if(auth()->id() === $member->user_id || auth()->user()->can('manageMembers', $organization))
                                            <form action="{{ route('organizations.members.destroy', [$organization, $member]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" variant="ghost" size="xs" class="text-red-600 hover:text-red-700" onclick="return confirm('Are you sure you want to remove this member?')">
                                                    {{ auth()->id() === $member->user_id ? 'Leave' : 'Remove' }}
                                                </flux:button>
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
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold">Pending Invites</h2>
                        <flux:badge color="amber">{{ count($invites) }} pending</flux:badge>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Sent By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Expires</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($invites as $invite)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium">{{ $invite->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $badgeColor = $invite->role === 'admin' ? 'purple' : ($invite->role === 'member' ? 'green' : 'blue');
                                            @endphp
                                            <flux:badge color="{{ $badgeColor }}" size="sm">{{ ucfirst($invite->role) }}</flux:badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-500">{{ $invite->sender->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-500">{{ $invite->expires_at->format('M d, Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <form action="{{ route('organizations.invites.cancel', [$organization, $invite]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" variant="ghost" size="xs" class="text-red-600 hover:text-red-700" onclick="return confirm('Are you sure you want to cancel this invitation?')">
                                                    Cancel
                                                </flux:button>
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