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
        $this->middleware('auth');
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
            'organization_id' => ['required_if:owner_type,organization', 'exists:organizations,id'],
        ]);
        
        $slug = Str::slug($validated['name']);
        
        $repository = new Repository();
        $repository->name = $validated['name'];
        $repository->slug = $slug;
        $repository->description = $validated['description'] ?? null;
        $repository->is_public = $validated['is_public'] ?? true;
        
        if ($validated['owner_type'] === 'user') {
            $repository->user_id = auth()->id();
        } else {
            $organization = Organization::findOrFail($validated['organization_id']);
            
            // Check if the user owns the organization
            if ($organization->owner_id !== auth()->id()) {
                abort(403, 'You do not have permission to create a repository in this organization.');
            }
            
            $repository->organization_id = $organization->id;
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
        $this->authorize('view', $repository);
        
        return view('repositories.show', compact('repository'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Repository $repository)
    {
        // Check if the user has permission to edit the repository
        $this->authorize('update', $repository);
        
        $organizations = auth()->user()->ownedOrganizations()->get();
        
        return view('repositories.edit', compact('repository', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Repository $repository)
    {
        // Check if the user has permission to update the repository
        $this->authorize('update', $repository);
        
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
     * Remove the specified resource from storage.
     */
    public function destroy(Repository $repository)
    {
        // Check if the user has permission to delete the repository
        $this->authorize('delete', $repository);
        
        $repository->delete();
        
        return redirect()->route('repositories.index')
            ->with('status', 'Repository deleted successfully.');
    }
}
