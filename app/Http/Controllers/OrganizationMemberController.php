<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationMemberController extends Controller
{
    /**
     * Display a listing of the members.
     */
    public function index(Organization $organization)
    {
        if (! auth()->user()->can('viewMembers', $organization)) {
            abort(403, 'You do not have permission to view members of this organization.');
        }

        $members = $organization->members()->with('user')->get();
        $invites = $organization->invites()
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->get();

        return view('organizations.members.index', compact('organization', 'members', 'invites'));
    }

    /**
     * Update the specified member role.
     */
    public function update(Request $request, Organization $organization, OrganizationMember $member)
    {
        if (! auth()->user()->can('manageMembers', $organization)) {
            abort(403, 'You do not have permission to update members in this organization.');
        }

        if ($member->organization_id !== $organization->id) {
            abort(404);
        }

        // Don't allow changing the role of the organization owner
        if ($member->user_id === $organization->owner_id) {
            return back()->with('error', 'Cannot change the role of the organization owner.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'member', 'viewer'])],
        ]);

        $member->role = $validated['role'];
        $member->save();

        return back()->with('status', 'Member role updated successfully.');
    }

    /**
     * Remove the specified member from the organization.
     */
    public function destroy(Organization $organization, OrganizationMember $member)
    {
        // Check permissions - either organization owners/admins, or the member themselves
        if (! auth()->user()->can('manageMembers', $organization) && 
            auth()->id() !== $member->user_id) {
            abort(403, 'You do not have permission to remove members from this organization.');
        }

        if ($member->organization_id !== $organization->id) {
            abort(404);
        }

        // Don't allow removing the organization owner
        if ($member->user_id === $organization->owner_id) {
            return back()->with('error', 'Cannot remove the organization owner from the organization.');
        }

        $member->delete();

        // If the user removed themselves, redirect to dashboard
        if (auth()->id() === $member->user_id) {
            return redirect()->route('dashboard')
                ->with('status', 'You have left the organization.');
        }

        return back()->with('status', 'Member removed successfully.');
    }
}
