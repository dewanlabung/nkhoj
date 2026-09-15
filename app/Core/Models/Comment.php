<?php

namespace App\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends BaseModel
{
    use HasFactory;

    const MODEL_TYPE = 'comment';

    protected $table = 'comments';
    protected $guarded = ['id'];

    protected $hidden = ['commentable_type', 'commentable_id', 'path'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'parent_id' => 'integer',
        'deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['depth', 'model_type'];

    /**
     * Get the user who created this comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the commentable model (polymorphic)
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get children comments
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    /**
     * Scope: Root comments only (no parent)
     */
    public function scopeRootOnly(Builder $builder): Builder
    {
        return $builder->whereNull('parent_id');
    }

    /**
     * Scope: Top-level comments (alias for rootOnly)
     */
    public function scopeTopLevel(Builder $builder): Builder
    {
        return $builder->whereNull('parent_id');
    }

    /**
     * Scope: Child comments only (has parent)
     */
    public function scopeChildrenOnly(Builder $builder): Builder
    {
        return $builder->whereNotNull('parent_id');
    }

    /**
     * Scope: Only approved (non-deleted) comments
     */
    public function scopeApproved(Builder $builder): Builder
    {
        return $builder->where('deleted', false);
    }

    /**
     * Get depth attribute (how nested this comment is)
     */
    public function getDepthAttribute(): int
    {
        if (!$this->path || !$this->parent_id) {
            return 0;
        }
        return count(explode('/', $this->getRawOriginal('path'))) - 1;
    }

    /**
     * Get model type attribute
     */
    public function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }

    /**
     * Generate hierarchical path for comment
     */
    public function generatePath(): void
    {
        if (!$this->parent_id) {
            $this->path = (string)$this->id;
        } else {
            $parent = self::find($this->parent_id);
            $this->path = ($parent?->path ?? $this->parent_id) . '/' . $this->id;
        }
        $this->saveQuietly();
    }

    /**
     * Get all ancestors
     */
    public function ancestors(): array
    {
        if (!$this->path) {
            return [];
        }
        $ids = array_filter(explode('/', $this->path));
        array_pop($ids); // Remove self
        return $ids;
    }

    /**
     * Check if comment is deleted
     */
    public function isDeleted(): bool
    {
        return (bool)$this->deleted;
    }

    /**
     * Soft delete: only soft-delete if has children, otherwise hard-delete
     */
    public function softOrHardDelete(): void
    {
        if ($this->children()->count() > 0) {
            $this->update(['deleted' => true]);
        } else {
            $this->forceDelete();
        }
    }

    /**
     * Convert to normalized array for API responses
     */
    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->content,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    /**
     * Convert to searchable array for full-text search
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'deleted' => $this->deleted,
            'commentable_id' => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
            'created_at' => $this->created_at?->timestamp ?? null,
            'updated_at' => $this->updated_at?->timestamp ?? null,
        ];
    }

    /**
     * Get filterable fields for datasource
     */
    public static function filterableFields(): array
    {
        return [
            'id',
            'parent_id',
            'user_id',
            'deleted',
            'commentable_id',
            'commentable_type',
            'created_at',
            'updated_at',
        ];
    }

}
