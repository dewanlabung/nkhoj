<?php

namespace App\Core\Actions;

use App\Core\Models\Comment;
use App\Core\Models\NotificationActivityLog;
use App\Models\User;
use Auth;
use Illuminate\Support\Arr;

class CrupdateComment
{
    /**
     * Create or update a comment with automatic notification to reply recipient
     */
    public function execute(
        array $data,
        Comment $initialComment = null,
    ): Comment {
        // Determine if creating or updating
        $isCreating = !$initialComment;
        $comment = $initialComment ?? new Comment([
            'user_id' => Auth::id(),
        ]);

        // Extract reply target if present
        $inReplyTo = Arr::get($data, 'inReplyTo');

        // Build attributes (exclude inReplyTo which is metadata)
        $attributes = Arr::except($data, 'inReplyTo');

        if ($inReplyTo) {
            $attributes['parent_id'] = $inReplyTo['id'];
        }

        if (isset($attributes['commentable_type'])) {
            $attributes['commentable_type'] = $data['commentable_type'];
        }

        // Save the comment
        $comment->fill($attributes)->save();

        // Generate hierarchical path if new
        if ($isCreating) {
            $comment->generatePath();
        }

        // Log activity
        NotificationActivityLog::log(
            Auth::user(),
            'comment',
            $isCreating ? 'created' : 'updated',
            'system',
            true,
            null,
            [
                'comment_id' => $comment->id,
                'parent_id' => $comment->parent_id,
                'content_length' => strlen($comment->content),
            ]
        );

        // Send notification to reply recipient if this is a new reply
        if (
            $isCreating &&
            $inReplyTo &&
            $inReplyTo['user']['id'] !== Auth::id()
        ) {
            $recipient = User::find($inReplyTo['user']['id']);
            if ($recipient) {
                event(new \App\Core\Events\CommentReplyCreated($comment, $inReplyTo));
            }
        }

        return $comment;
    }
}
