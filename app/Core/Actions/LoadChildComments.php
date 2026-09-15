<?php

namespace App\Core\Actions;

use App\Models\Blog\Comment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LoadChildComments
{
    /**
     * Load nested child comments for given root comments
     * Uses path-based queries for efficiency
     */
    public function execute(
        Model $commentable,
        Collection $rootComments,
    ): Collection {
        if ($rootComments->isEmpty()) {
            return $rootComments;
        }

        // Build OR clause for all root paths
        $paths = $rootComments
            ->map(function (Comment $comment) {
                $path = $comment->getRawOriginal('path');
                return "LIKE '$path%'";
            })
            ->implode(' OR path ');

        // Single query for all nested comments
        $childComments = app(Comment::class)
            ->with(['user' => fn($builder) => $builder->select(['id', 'name', 'username', 'avatar_url'])])
            ->where('commentable_id', $commentable->id)
            ->where('commentable_type', $commentable->getMorphClass())
            ->childrenOnly()
            ->where(fn(Builder $builder) => $builder->whereRaw("path $paths"))
            ->orderBy('path', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Splice children into array right after their parents
        // This maintains parent-child order for sequential rendering
        $childComments->each(function ($child) use ($rootComments) {
            $parentIndex = $rootComments->search(
                fn($parent) => $parent['id'] === $child['parent_id'],
            );

            if ($parentIndex !== false) {
                $rootComments->splice($parentIndex + 1, 0, [$child]);
            }
        });

        return $rootComments;
    }
}
