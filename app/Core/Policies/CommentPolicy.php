<?php

namespace App\Core\Policies;

use App\Core\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Index: View all comments or only user's own
     */
    public function index(?User $user, $userId = null): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasPermission('comments.view') ||
            $user->id === (int) $userId;
    }

    /**
     * Show: View specific comment
     */
    public function show(?User $user, Comment $comment): bool
    {
        if (!$user) {
            return $comment->deleted === false;
        }

        return $user->hasPermission('comments.view') ||
            $comment->user_id === $user->id;
    }

    /**
     * Store: Create new comment
     */
    public function store(User $user): bool
    {
        return $user->id &&
               !$user->is_banned &&
               $user->hasPermission('comments.create');
    }

    /**
     * Update: Edit comment (own or admin)
     */
    public function update(User $user, ?Comment $comment = null): bool
    {
        if (!$user || $user->is_banned) {
            return false;
        }

        return $user->hasPermission('comments.update') ||
            ($comment && $comment->user_id === $user->id);
    }

    /**
     * Destroy: Delete comments (own or admin)
     */
    public function destroy(User $user, $commentIds): bool
    {
        if (!$user || $user->is_banned) {
            return false;
        }

        // Admin can delete anything
        if ($user->hasPermission('comments.delete')) {
            return true;
        }

        // User can delete only their own
        $count = Comment::whereIn('id', (array)$commentIds)
            ->where('user_id', $user->id)
            ->count();

        return $count === count((array)$commentIds);
    }

    /**
     * Restore: Restore soft-deleted comment
     */
    public function restore(User $user, Comment $comment): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasPermission('comments.delete') ||
            $comment->user_id === $user->id;
    }
}
