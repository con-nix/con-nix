<x-layouts.app :title="'Transfer ' . $repository->name">
    <div class="max-w-2xl mx-auto p-6">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Transfer Repository</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Transfer ownership of <strong>{{ $repository->name }}</strong> to another owner.
            </p>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6 dark:bg-amber-900/20 dark:border-amber-800">
            <div class="flex">
                <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                        Warning
                    </h3>
                    <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                        <p>Transferring a repository will:</p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>Change the owner of the repository</li>
                            <li>Move all repository data to the new owner</li>
                            <li>This action cannot be undone</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('repositories.transfer', $repository) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Transfer to</label>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="radio" name="transfer_type" value="user" class="mr-3" {{ old('transfer_type', 'user') === 'user' ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Your personal account</span>
                    </label>
                    @if($organizations->count() > 0)
                        <label class="flex items-center">
                            <input type="radio" name="transfer_type" value="organization" class="mr-3" {{ old('transfer_type') === 'organization' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 dark:text-gray-300">An organization you own</span>
                        </label>
                    @endif
                </div>
                @error('transfer_type')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            @if($organizations->count() > 0)
                <div id="organization-select" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Organization</label>
                    <select name="organization_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="">Select an organization</option>
                        @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>
                                {{ $organization->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('organization_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm transfer</label>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Type <strong>{{ $repository->name }}</strong> to confirm the transfer.
                </p>
                <input 
                    type="text" 
                    name="confirmation" 
                    value="{{ old('confirmation') }}" 
                    placeholder="Repository name" 
                    required 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                >
                @error('confirmation')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('repositories.show', $repository) }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Transfer Repository
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const transferTypeRadios = document.querySelectorAll('input[name="transfer_type"]');
            const organizationSelect = document.getElementById('organization-select');
            
            function toggleOrganizationSelect() {
                const selectedValue = document.querySelector('input[name="transfer_type"]:checked')?.value;
                if (organizationSelect) {
                    organizationSelect.style.display = selectedValue === 'organization' ? 'block' : 'none';
                }
            }
            
            transferTypeRadios.forEach(radio => {
                radio.addEventListener('change', toggleOrganizationSelect);
            });
            
            // Initialize on page load
            toggleOrganizationSelect();
        });
    </script>
</x-layouts.app>