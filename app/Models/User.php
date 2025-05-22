<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Get the repositories that belong to the user.
     */
    public function repositories()
    {
        return $this->hasMany(Repository::class);
    }

    /**
     * Get the organizations owned by the user.
     */
    public function ownedOrganizations()
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    /**
     * Get the organization memberships of the user.
     */
    public function organizationMemberships(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    /**
     * Get the organizations that the user is a member of.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the organization invites sent by the user.
     */
    public function sentInvites(): HasMany
    {
        return $this->hasMany(OrganizationInvite::class, 'sender_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the activities performed by the user.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get the users that this user is following.
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Get the users that are following this user.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Get the follow relationships for this user.
     */
    public function followRelationships(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /**
     * Get all repositories accessible to the user
     * (personal repositories and repositories from owned and member organizations)
     */
    public function allRepositories()
    {
        $personalRepos = $this->repositories();

        $ownedOrgIds = $this->ownedOrganizations()->pluck('organizations.id');
        $memberOrgIds = $this->organizations()->pluck('organizations.id');
        
        $allOrgIds = $ownedOrgIds->merge($memberOrgIds)->unique();

        return Repository::where(function ($query) use ($allOrgIds) {
            $query->where('user_id', $this->id)
                ->orWhereIn('organization_id', $allOrgIds);
        });
    }

    /**
     * Check if this user is following another user.
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Check if this user is followed by another user.
     */
    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    /**
     * Follow another user.
     */
    public function follow(User $user): void
    {
        if (!$this->isFollowing($user) && $this->id !== $user->id) {
            $this->following()->attach($user->id);
            
            // Create notification for the followed user
            $user->createNotification('user_follow', "{$this->name} started following you", null, [
                'follower' => $this->only(['id', 'name', 'email']),
            ]);
        }
    }

    /**
     * Unfollow another user.
     */
    public function unfollow(User $user): void
    {
        $this->following()->detach($user->id);
    }

    /**
     * Get the count of users this user is following.
     */
    public function getFollowingCountAttribute(): int
    {
        return $this->following()->count();
    }

    /**
     * Get the count of users following this user.
     */
    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->count();
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Create a notification for this user.
     */
    public function createNotification(string $type, string $title, ?string $message = null, ?array $data = null, ?string $actionUrl = null): Notification
    {
        return $this->notifications()->create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Record an activity for this user.
     */
    public function recordActivity(string $type, string $description, $subject = null, ?array $properties = null): Activity
    {
        return $this->activities()->create([
            'type' => $type,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'properties' => $properties,
        ]);
    }

    /**
     * Get activity feed for this user (activities from users they follow).
     */
    public function getActivityFeed(int $limit = 50)
    {
        $followingIds = $this->following()->pluck('users.id')->toArray();
        $followingIds[] = $this->id; // Include own activities
        
        return Activity::byUsers($followingIds)
            ->with(['user', 'subject'])
            ->recent()
            ->limit($limit)
            ->get();
    }
}
