<?php

namespace App\Core\Events;

use App\Core\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentReplyCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance
     */
    public function __construct(
        public Comment $comment,
        public array $originalComment,
    ) {}
}
