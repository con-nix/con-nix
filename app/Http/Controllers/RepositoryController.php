<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RepositoryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Auth middleware is applied in routes/web.php
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $repositories = auth()->user()->allRepositories()->latest()->paginate(12);

        return view('repositories.index', compact('repositories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizations = auth()->user()->ownedOrganizations()->get();

        return view('repositories.create', compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_public' => ['boolean'],
            'owner_type' => ['required', 'in:user,organization'],
            'organization_id' => ['nullable', 'required_if:owner_type,organization', 'exists:organizations,id'],
        ]);

        $slug = Str::slug($validated['name']);

        $repository = new Repository;
        $repository->name = $validated['name'];
        $repository->slug = $slug;
        $repository->description = $validated['description'] ?? null;
        $repository->is_public = $validated['is_public'] ?? true;

        if ($validated['owner_type'] === 'user') {
            $repository->user_id = auth()->id();
            $repository->organization_id = null;
        } else {
            // Get the organization ID, handling possible null cases
            $organizationId = $validated['organization_id'] ?? null;

            if (! $organizationId) {
                return back()
                    ->withInput()
                    ->withErrors(['organization_id' => 'Please select an organization.']);
            }

            $organization = Organization::find($organizationId);

            if (! $organization) {
                return back()
                    ->withInput()
                    ->withErrors(['organization_id' => 'The selected organization does not exist.']);
            }

            // Check if the user owns the organization
            if ($organization->owner_id !== auth()->id()) {
                return back()
                    ->withInput()
                    ->withErrors(['organization_id' => 'You do not have permission to create a repository in this organization.']);
            }

            $repository->organization_id = $organization->id;
            $repository->user_id = null;
        }

        $repository->save();

        return redirect()->route('repositories.show', $repository)
            ->with('status', 'Repository created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Repository $repository)
    {
        // Check if the user has access to the repository
        if (! auth()->user()->can('view', $repository)) {
            abort(403, 'You do not have permission to view this repository.');
        }

        return view('repositories.show', compact('repository'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Repository $repository)
    {
        // Check if the user has permission to edit the repository
        if (! auth()->user()->can('update', $repository)) {
            abort(403, 'You do not have permission to edit this repository.');
        }

        $organizations = auth()->user()->ownedOrganizations()->get();

        return view('repositories.edit', compact('repository', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Repository $repository)
    {
        // Check if the user has permission to update the repository
        if (! auth()->user()->can('update', $repository)) {
            abort(403, 'You do not have permission to update this repository.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_public' => ['boolean'],
        ]);

        $repository->name = $validated['name'];
        $repository->slug = Str::slug($validated['name']);
        $repository->description = $validated['description'] ?? null;
        $repository->is_public = $validated['is_public'] ?? true;
        $repository->save();

        return redirect()->route('repositories.show', $repository)
            ->with('status', 'Repository updated successfully.');
    }

    /**
     * Show the form for transferring the repository.
     */
    public function transferForm(Repository $repository)
    {
        // Check if the user has permission to transfer the repository
        if (! auth()->user()->can('delete', $repository)) {
            abort(403, 'You do not have permission to transfer this repository.');
        }

        $organizations = auth()->user()->ownedOrganizations()->get();

        return view('repositories.transfer', compact('repository', 'organizations'));
    }

    /**
     * Transfer the repository to another owner.
     */
    public function transfer(Request $request, Repository $repository)
    {
        // Check if the user has permission to transfer the repository
        if (! auth()->user()->can('delete', $repository)) {
            abort(403, 'You do not have permission to transfer this repository.');
        }

        $validated = $request->validate([
            'transfer_type' => ['required', 'in:user,organization'],
            'organization_id' => ['nullable', 'required_if:transfer_type,organization', 'exists:organizations,id'],
            'confirmation' => ['required', 'string'],
        ]);

        // Verify confirmation matches repository name
        if ($validated['confirmation'] !== $repository->name) {
            return back()
                ->withInput()
                ->withErrors(['confirmation' => 'Repository name confirmation does not match.']);
        }

        if ($validated['transfer_type'] === 'user') {
            // Transfer to user's personal account
            $repository->user_id = auth()->id();
            $repository->organization_id = null;
        } else {
            // Transfer to organization
            $organizationId = $validated['organization_id'];
            $organization = Organization::find($organizationId);

            if (! $organization) {
                return back()
                    ->withInput()
                    ->withErrors(['organization_id' => 'The selected organization does not exist.']);
            }

            // Check if the user owns the organization
            if ($organization->owner_id !== auth()->id()) {
                return back()
                    ->withInput()
                    ->withErrors(['organization_id' => 'You can only transfer to organizations you own.']);
            }

            $repository->organization_id = $organization->id;
            $repository->user_id = null;
        }

        $repository->save();

        $transferTo = $validated['transfer_type'] === 'user'
            ? 'your personal account'
            : $organization->name;

        return redirect()->route('repositories.show', $repository)
            ->with('status', "Repository transferred to {$transferTo} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Repository $repository)
    {
        // Check if the user has permission to delete the repository
        if (! auth()->user()->can('delete', $repository)) {
            abort(403, 'You do not have permission to delete this repository.');
        }

        $repository->delete();

        return redirect()->route('repositories.index')
            ->with('status', 'Repository deleted successfully.');
    }
}
