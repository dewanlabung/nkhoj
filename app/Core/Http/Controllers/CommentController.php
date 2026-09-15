<?php

namespace App\Core\Http\Controllers;

use App\Core\Actions\CrupdateComment;
use App\Core\Actions\PaginateModelComments;
use App\Models\Blog\Comment;
use App\Core\Policies\CommentPolicy;
use App\Core\Requests\CrupdateCommentRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function __construct(
        protected Comment $comment,
        protected CrupdateComment $crupdateAction,
        protected PaginateModelComments $paginateAction,
    ) {
        // Register policy
        Gate::policy(Comment::class, CommentPolicy::class);
    }

    /**
     * Get paginated comments for a commentable model
     */
    public function index(): JsonResponse
    {
        $userId = request('userId');
        $this->authorize('index', [Comment::class, $userId]);

        $builder = $this->comment
            ->with(['user' => fn($q) => $q->select(['id', 'name', 'username', 'avatar_url'])]);

        // Filter by commentable if specified
        if (request('commentable_id') && request('commentable_type')) {
            $builder->where([
                'commentable_id' => request('commentable_id'),
                'commentable_type' => request('commentable_type'),
            ]);
        }

        $pagination = $builder->paginate(request('perPage', 25));

        return response()->json([
            'success' => true,
            'data' => $pagination,
        ]);
    }

    /**
     * Get comments for a specific commentable model
     */
    public function forCommentable(): JsonResponse
    {
        $modelType = request('commentable_type');
        $modelId = request('commentable_id');

        if (!$modelType || !$modelId) {
            abort(404);
        }

        // Resolve model
        $modelClass = $this->resolveModelFromType($modelType);
        $commentable = $modelClass->findOrFail($modelId);

        // Get paginated comments with nested children
        $pagination = $this->paginateAction->execute($commentable);

        return response()->json([
            'success' => true,
            'data' => $pagination,
        ]);
    }

    /**
     * Get a specific comment
     */
    public function show(Comment $comment): JsonResponse
    {
        $this->authorize('show', $comment);

        return response()->json([
            'success' => true,
            'data' => $comment->load(['user']),
        ]);
    }

    /**
     * Create a new comment
     */
    public function store(CrupdateCommentRequest $request): JsonResponse
    {
        $this->authorize('store', Comment::class);

        $comment = $this->crupdateAction->execute($request->validated());

        return response()->json([
            'success' => true,
            'data' => $comment->load(['user']),
            'message' => 'Comment created successfully',
        ], 201);
    }

    /**
     * Update a comment
     */
    public function update(Comment $comment, CrupdateCommentRequest $request): JsonResponse
    {
        $this->authorize('update', $comment);

        $updated = $this->crupdateAction->execute(
            $request->validated(),
            $comment
        );

        return response()->json([
            'success' => true,
            'data' => $updated->load(['user']),
            'message' => 'Comment updated successfully',
        ]);
    }

    /**
     * Delete one or more comments
     */
    public function destroy(string $ids): JsonResponse
    {
        $commentIds = array_map('intval', explode(',', $ids));
        $this->authorize('destroy', [Comment::class, $commentIds]);

        $allDeleted = [];
        $allMarkedAsDeleted = [];

        $this->comment
            ->whereIn('id', $commentIds)
            ->chunkById(50, function ($comments) use (&$allDeleted, &$allMarkedAsDeleted) {
                foreach ($comments as $comment) {
                    if ($comment->children()->count() > 0) {
                        $comment->update(['deleted' => true]);
                        $allMarkedAsDeleted[] = $comment->id;
                    } else {
                        $comment->forceDelete();
                        $allDeleted[] = $comment->id;
                    }
                }
            });

        return response()->json([
            'success' => true,
            'deleted' => $allDeleted,
            'marked_as_deleted' => $allMarkedAsDeleted,
            'message' => 'Comments deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted comment
     */
    public function restore(Comment $comment): JsonResponse
    {
        $this->authorize('restore', $comment);

        $comment->update(['deleted' => false]);

        return response()->json([
            'success' => true,
            'data' => $comment,
            'message' => 'Comment restored successfully',
        ]);
    }

    /**
     * Resolve model class from type string
     */
    protected function resolveModelFromType(string $modelType): Model
    {
        $map = [
            'post' => \App\Models\Blog\Post::class,
            'article' => \App\Models\Article::class,
            // Add more model mappings as needed
        ];

        if (!isset($map[$modelType])) {
            abort(404, "Unknown model type: $modelType");
        }

        return app($map[$modelType]);
    }
}
