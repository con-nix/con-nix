<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Models\OrganizationMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizationInviteController extends Controller
{
    /**
     * Show form to invite a member to an organization.
     */
    public function create(Organization $organization)
    {
        if (! auth()->user()->can('inviteMembers', $organization)) {
            abort(403, 'You do not have permission to invite members to this organization.');
        }

        return view('organizations.invites.create', compact('organization'));
    }

    /**
     * Store a new invite.
     */
    public function store(Request $request, Organization $organization)
    {
        if (! auth()->user()->can('inviteMembers', $organization)) {
            abort(403, 'You do not have permission to invite members to this organization.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'member', 'viewer'])],
        ]);

        // Check if the user is already a member
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser && $organization->hasMember($existingUser)) {
            return back()->withErrors([
                'email' => 'This user is already a member of the organization.',
            ])->withInput();
        }

        // Check if there's already a pending invite
        $existingInvite = $organization->invites()
            ->where('email', $validated['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvite) {
            return back()->withErrors([
                'email' => 'An invitation has already been sent to this email.',
            ])->withInput();
        }

        // Create the invite
        $invite = new OrganizationInvite();
        $invite->organization_id = $organization->id;
        $invite->sender_id = auth()->id();
        $invite->email = $validated['email'];
        $invite->role = $validated['role'];
        $invite->token = OrganizationInvite::generateToken();
        $invite->expires_at = Carbon::now()->addDays(7);
        $invite->save();

        // TODO: Send email notification

        return redirect()->route('organizations.show', $organization)
            ->with('status', 'Invitation sent successfully.');
    }

    /**
     * Show the invite.
     */
    public function show($token)
    {
        $invite = OrganizationInvite::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $organization = $invite->organization;

        return view('organizations.invites.show', compact('invite', 'organization'));
    }

    /**
     * Accept an invite.
     */
    public function accept(Request $request, $token)
    {
        // User must be logged in to accept an invite
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('status', 'Please log in to accept the invitation.');
        }

        $invite = OrganizationInvite::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Check if the logged-in user's email matches the invite email
        if (auth()->user()->email !== $invite->email) {
            abort(403, 'This invitation was not sent to your email address.');
        }

        // Check if the user is already a member
        if ($invite->organization->hasMember(auth()->user())) {
            $invite->accepted_at = now();
            $invite->save();
            
            return redirect()->route('organizations.show', $invite->organization)
                ->with('status', 'You are already a member of this organization.');
        }

        // Create the membership
        $member = new OrganizationMember();
        $member->organization_id = $invite->organization_id;
        $member->user_id = auth()->id();
        $member->role = $invite->role;
        $member->save();

        // Mark the invite as accepted
        $invite->accepted_at = now();
        $invite->save();

        return redirect()->route('organizations.show', $invite->organization)
            ->with('status', 'You have joined the organization successfully.');
    }

    /**
     * Decline an invite.
     */
    public function decline(Request $request, $token)
    {
        $invite = OrganizationInvite::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Just delete the invite
        $invite->delete();

        return redirect()->route('dashboard')
            ->with('status', 'Invitation declined successfully.');
    }

    /**
     * Cancel an invite.
     */
    public function cancel(Organization $organization, OrganizationInvite $invite)
    {
        if (! auth()->user()->can('inviteMembers', $organization)) {
            abort(403, 'You do not have permission to cancel invites for this organization.');
        }

        if ($invite->organization_id !== $organization->id) {
            abort(404);
        }

        $invite->delete();

        return redirect()->route('organizations.show', $organization)
            ->with('status', 'Invitation cancelled successfully.');
    }
}
