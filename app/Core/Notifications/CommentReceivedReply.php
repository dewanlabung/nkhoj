<?php

namespace App\Core\Notifications;

use App\Core\Models\Comment;
use App\Models\User;
use Illuminate\Support\Str;

class CommentReceivedReply extends BaseNotification
{
    public const NOTIF_ID = 'comment_replied';

    public function __construct(
        public Comment $comment,
        public ?User $originalCommentAuthor = null,
    ) {}

    public function notificationType(): string
    {
        return 'comment_reply';
    }

    protected function subject(User $notifiable): string
    {
        return "{$this->comment->user->displayName()} replied to your comment";
    }

    protected function body(User $notifiable): string
    {
        return Str::limit($this->comment->content, 180);
    }

    protected function actionUrl(): ?string
    {
        if (!$this->comment->commentable) {
            return null;
        }

        $commentable = $this->comment->commentable;
        $baseUrl = match ($commentable->getMorphClass()) {
            'post' => "/posts/{$commentable->id}",
            'article' => "/articles/{$commentable->id}",
            default => null,
        };

        return $baseUrl ? "{$baseUrl}#comment-{$this->comment->id}" : null;
    }

    protected function actionLabel(): string
    {
        return 'View Reply';
    }

    protected function icon(): ?string
    {
        return 'comment-reply';
    }

    public function toArray(User $notifiable): array
    {
        return array_merge(parent::toArray($notifiable), [
            'comment_id' => $this->comment->id,
            'author_name' => $this->comment->user->displayName(),
            'author_avatar' => $this->comment->user->avatar_url,
            'commentable_type' => $this->comment->commentable_type,
            'commentable_id' => $this->comment->commentable_id,
        ]);
    }
}
