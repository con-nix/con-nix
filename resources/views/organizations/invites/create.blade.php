<x-layouts.app :title="'Invite Member - ' . $organization->name">
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="mb-8">
            <a href="{{ route('organizations.members.index', $organization) }}" class="text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300">
                &larr; Back to Members
            </a>
            <h1 class="mt-2 text-2xl font-bold">Invite a Member</h1>
            <p class="mt-1 text-neutral-600 dark:text-neutral-400">
                {{ $organization->name }}
            </p>
        </div>

        <div class="rounded-md bg-white p-6 shadow-sm dark:bg-neutral-800">
            <form action="{{ route('organizations.invites.store', $organization) }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            Email Address
                        </label>
                        <div class="mt-1">
                            <input type="email" name="email" id="email" required
                                   class="block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 sm:text-sm"
                                   placeholder="colleague@example.com"
                                   value="{{ old('email') }}">
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            Role
                        </label>
                        <div class="mt-1">
                            <select name="role" id="role" required
                                    class="block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 sm:text-sm">
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="member" {{ old('role') === 'member' || old('role') === null ? 'selected' : '' }}>Member</option>
                                <option value="viewer" {{ old('role') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                            </select>
                        </div>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-md bg-neutral-50 p-4 dark:bg-neutral-900">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-neutral-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Role Permissions</h3>
                                <div class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                                    <ul class="list-disc space-y-1 pl-5">
                                        <li><strong>Admin:</strong> Full access to manage organization settings, members, and repositories</li>
                                        <li><strong>Member:</strong> Can create and manage repositories, but cannot modify organization settings</li>
                                        <li><strong>Viewer:</strong> Can only view organization content, no modification rights</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Send Invitation
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>