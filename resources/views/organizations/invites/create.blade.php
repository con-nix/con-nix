<x-layouts.app :title="'Invite Member - ' . $organization->name">
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="mb-8">
            <flux:button :href="route('organizations.members.index', $organization)" variant="ghost" icon="arrow-left">
                Back to Members
            </flux:button>
            <flux:heading size="xl" class="mt-4">Invite a Member</flux:heading>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                {{ $organization->name }}
            </p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-6">
            <form action="{{ route('organizations.invites.store', $organization) }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <flux:field>
                        <flux:label>Email Address</flux:label>
                        <flux:input type="email" name="email" placeholder="colleague@example.com" value="{{ old('email') }}" required />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:select name="role" required>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="member" {{ old('role') === 'member' || old('role') === null ? 'selected' : '' }}>Member</option>
                            <option value="viewer" {{ old('role') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                        </flux:select>
                        <flux:error name="role" />
                    </flux:field>

                    <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="size-5 text-zinc-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium">Role Permissions</h4>
                                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 space-y-2">
                                    <div><flux:badge color="purple" size="xs">Admin</flux:badge> Full access to manage organization settings, members, and repositories</div>
                                    <div><flux:badge color="green" size="xs">Member</flux:badge> Can create and manage repositories, but cannot modify organization settings</div>
                                    <div><flux:badge color="blue" size="xs">Viewer</flux:badge> Can only view organization content, no modification rights</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" icon="envelope">
                            Send Invitation
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>