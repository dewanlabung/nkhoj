<?php

namespace App\Core\Actions;

use App\Models\Blog\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PaginateModelComments
{
    /**
     * Get paginated comments with all nested children
     */
    public function execute(Model $commentable): array
    {
        // Step 1: Paginate root comments only
        $pagination = $commentable
            ->comments()
            ->rootOnly()
            ->with([
                'user' => fn($builder) => $builder->select(['id', 'name', 'username', 'avatar_url']),
            ])
            ->paginate(request('perPage') ?? 25);

        // Step 2: Load all nested children for paginated roots
        $comments = app(LoadChildComments::class)->execute(
            $commentable,
            Collection::make($pagination->items()),
        );

        // Step 3: Transform for API response
        $comments->transform(function (Comment $comment) {
            // Hide deleted content but keep comment for thread continuity
            if ($comment->deleted) {
                $comment->content = '[Deleted Comment]';
            }
            return $comment;
        });

        // Step 4: Return pagination with transformed data
        $pagination = $pagination->toArray();
        $pagination['data'] = $comments->values()->toArray();

        return $pagination;
    }
}
