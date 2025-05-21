<x-layouts.app :title="__('Create Repository')">
    <div class="mb-8">
        <h1 class="text-2xl font-bold">Create Repository</h1>
        <p class="mt-1 text-neutral-600 dark:text-neutral-400">Create a new repository for your code</p>
    </div>

    <div class="mx-auto max-w-3xl">
        <form action="{{ route('repositories.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Repository Name -->
            <div>
                <x-input-label for="name" :value="__('Repository Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Repository Description -->
            <div>
                <x-input-label for="description" :value="__('Description (Optional)')" />
                <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-indigo-600 dark:focus:ring-indigo-600">{{ old('description') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>

            <!-- Visibility Settings -->
            <div>
                <x-input-label :value="__('Visibility')" />
                <div class="mt-2 space-y-3">
                    <div class="flex items-start">
                        <div class="flex h-5 items-center">
                            <input id="is_public" name="is_public" type="checkbox" value="1" class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-600 dark:border-neutral-700 dark:bg-neutral-900 dark:ring-offset-neutral-900 dark:focus:ring-indigo-600" {{ old('is_public', '1') == '1' ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3">
                            <label for="is_public" class="font-medium text-neutral-900 dark:text-white">Public</label>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Anyone can see this repository. You choose who can commit.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Owner Selection -->
            <div>
                <x-input-label :value="__('Owner')" />
                <div class="mt-2 space-y-3">
                    <div class="flex items-start">
                        <div class="flex h-5 items-center">
                            <input id="owner_user" name="owner_type" type="radio" value="user" class="h-4 w-4 border-neutral-300 text-indigo-600 focus:ring-indigo-600 dark:border-neutral-700 dark:bg-neutral-900 dark:ring-offset-neutral-900 dark:focus:ring-indigo-600" {{ old('owner_type', 'user') == 'user' ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3">
                            <label for="owner_user" class="font-medium text-neutral-900 dark:text-white">{{ auth()->user()->name }}</label>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Personal repository</p>
                        </div>
                    </div>

                    @if(count($organizations) > 0)
                        @foreach($organizations as $organization)
                        <div class="flex items-start">
                            <div class="flex h-5 items-center">
                                <input id="owner_org_{{ $organization->id }}" name="owner_type" type="radio" value="organization" data-org-id="{{ $organization->id }}" class="h-4 w-4 border-neutral-300 text-indigo-600 focus:ring-indigo-600 dark:border-neutral-700 dark:bg-neutral-900 dark:ring-offset-neutral-900 dark:focus:ring-indigo-600" {{ old('owner_type') == 'organization' && old('organization_id') == $organization->id ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3">
                                <label for="owner_org_{{ $organization->id }}" class="font-medium text-neutral-900 dark:text-white">{{ $organization->name }}</label>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Organization repository</p>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
                
                <!-- Hidden field for organization_id -->
                <input type="hidden" name="organization_id" id="organization_id" value="{{ old('organization_id') }}">
                
                @if($errors->has('owner_type') || $errors->has('organization_id'))
                    <div class="mt-2 rounded-md bg-red-50 p-2 dark:bg-red-900/30">
                        <x-input-error class="mt-0" :messages="$errors->get('owner_type')" />
                        <x-input-error class="mt-0" :messages="$errors->get('organization_id')" />
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-neutral-700 shadow-sm transition duration-150 ease-in-out hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-800">
                    Cancel
                </a>

                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-700 dark:focus:ring-offset-neutral-800">
                    Create Repository
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle organization selection
            const orgRadios = document.querySelectorAll('input[name="owner_type"][value="organization"]');
            const orgIdInput = document.getElementById('organization_id');
            
            // Initialize organization_id based on the currently selected radio
            function updateOrganizationId() {
                const selectedOrgRadio = document.querySelector('input[name="owner_type"][value="organization"]:checked');
                
                if (selectedOrgRadio) {
                    orgIdInput.value = selectedOrgRadio.getAttribute('data-org-id');
                    console.log('Setting organization ID to:', orgIdInput.value);
                } else if (document.getElementById('owner_user').checked) {
                    orgIdInput.value = '';
                    console.log('Clearing organization ID');
                }
            }
            
            // Set initial value when page loads
            updateOrganizationId();
            
            // Update when any radio button changes
            orgRadios.forEach(radio => {
                radio.addEventListener('change', updateOrganizationId);
            });
            
            // Clear organization_id when user selects personal repository
            const userRadio = document.getElementById('owner_user');
            userRadio.addEventListener('change', function() {
                if (this.checked) {
                    orgIdInput.value = '';
                    console.log('Clearing organization ID - user selected');
                }
            });
        });
    </script>
</x-layouts.app>