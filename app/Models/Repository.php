<?php

namespace App\Models;

use Database\Factories\RepositoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repository extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return RepositoryFactory::new();
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
        'is_public',
        'user_id',
        'organization_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the user that owns the repository.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that owns the repository.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the owner of the repository (either user or organization).
     */
    public function owner()
    {
        return $this->organization_id ? $this->organization : $this->user;
    }

    /**
     * Get the owner name of the repository.
     */
    public function getOwnerNameAttribute(): string
    {
        return $this->organization_id ? $this->organization->name : $this->user->name;
    }

    /**
     * Scope a query to only include public repositories.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to search repositories by name or description.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by owner type.
     */
    public function scopeByOwnerType(Builder $query, ?string $ownerType): Builder
    {
        if (empty($ownerType)) {
            return $query;
        }

        return match ($ownerType) {
            'user' => $query->whereNotNull('user_id'),
            'organization' => $query->whereNotNull('organization_id'),
            default => $query,
        };
    }
}
