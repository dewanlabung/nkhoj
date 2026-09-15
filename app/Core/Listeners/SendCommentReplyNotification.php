<?php

namespace App\Core\Listeners;

use App\Core\Events\CommentReplyCreated;
use App\Core\Models\NotificationActivityLog;
use App\Core\Notifications\CommentReceivedReply;
use App\Models\User;

class SendCommentReplyNotification
{
    /**
     * Handle the event
     */
    public function handle(CommentReplyCreated $event): void
    {
        // Get the original comment author
        $originalAuthor = User::find($event->originalComment['user']['id']);

        if (!$originalAuthor) {
            NotificationActivityLog::log(
                null,
                'comment_replied',
                'skipped',
                'system',
                false,
                'Original comment author not found'
            );
            return;
        }

        try {
            // Send notification
            $originalAuthor->notify(
                new CommentReceivedReply($event->comment, $originalAuthor)
            );

            // Log successful notification
            NotificationActivityLog::log(
                $originalAuthor,
                'comment_replied',
                'sent',
                'system',
                true,
                null,
                [
                    'comment_id' => $event->comment->id,
                    'reply_author_id' => $event->comment->user_id,
                ]
            );
        } catch (\Exception $e) {
            // Log failed notification
            NotificationActivityLog::log(
                $originalAuthor,
                'comment_replied',
                'failed',
                'system',
                false,
                $e->getMessage()
            );
        }
    }
}
