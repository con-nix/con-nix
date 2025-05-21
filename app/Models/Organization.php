<?php

namespace App\Models;

use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return OrganizationFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
    ];

    /**
     * Get the owner of the organization.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the repositories for the organization.
     */
    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class);
    }

    /**
     * Get the members of the organization.
     */
    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    /**
     * Get the users who are members of the organization.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the invites for the organization.
     */
    public function invites(): HasMany
    {
        return $this->hasMany(OrganizationInvite::class);
    }

    /**
     * Check if a user is a member of the organization.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Get a user's membership in the organization.
     */
    public function getMember(User $user): ?OrganizationMember
    {
        return $this->members()->where('user_id', $user->id)->first();
    }

    /**
     * Check if a user is an admin of the organization.
     */
    public function hasAdmin(User $user): bool
    {
        $member = $this->getMember($user);
        
        return $member && $member->isAdmin();
    }

    /**
     * Check if a user is the owner of the organization.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }
}
