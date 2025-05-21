<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends Controller
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
        $organizations = auth()->user()->ownedOrganizations()->latest()->paginate(12);

        return view('organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (! auth()->user()->can('create', Organization::class)) {
            abort(403, 'You do not have permission to create an organization.');
        }

        return view('organizations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('create', Organization::class)) {
            abort(403, 'You do not have permission to create an organization.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $slug = Str::slug($validated['name']);

        $organization = new Organization;
        $organization->name = $validated['name'];
        $organization->slug = $slug;
        $organization->description = $validated['description'] ?? null;
        $organization->owner_id = auth()->id();
        $organization->save();

        return redirect()->route('organizations.show', $organization)
            ->with('status', 'Organization created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        if (! auth()->user()->can('view', $organization)) {
            abort(403, 'You do not have permission to view this organization.');
        }

        return view('organizations.show', compact('organization'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        if (! auth()->user()->can('update', $organization)) {
            abort(403, 'You do not have permission to edit this organization.');
        }

        return view('organizations.edit', compact('organization'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        if (! auth()->user()->can('update', $organization)) {
            abort(403, 'You do not have permission to update this organization.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $organization->name = $validated['name'];
        $organization->slug = Str::slug($validated['name']);
        $organization->description = $validated['description'] ?? null;
        $organization->save();

        return redirect()->route('organizations.show', $organization)
            ->with('status', 'Organization updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        if (! auth()->user()->can('delete', $organization)) {
            abort(403, 'You do not have permission to delete this organization.');
        }

        // Check if the organization has any repositories
        if ($organization->repositories()->count() > 0) {
            return back()->with('error', 'Cannot delete an organization that has repositories. Please delete all repositories first.');
        }

        $organization->delete();

        return redirect()->route('organizations.index')
            ->with('status', 'Organization deleted successfully.');
    }
}
