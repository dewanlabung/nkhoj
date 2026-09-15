<?php

namespace App\Domains\Blog\Commands;

use App\Models\Notification;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PublishScheduledPosts extends Command
{
    protected $signature   = 'posts:publish-scheduled';
    protected $description = 'Publish posts whose scheduled_at has passed';

    public function handle(): void
    {
        $posts = Post::with('author')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', Carbon::now())
            ->get();

        foreach ($posts as $post) {
            $post->update(['status' => 'published', 'published_at' => Carbon::now()]);

            $followers = $post->author->followers()->pluck('users.id');
            foreach ($followers as $followerId) {
                Notification::create([
                    'user_id' => $followerId,
                    'type'    => 'new_post',
                    'data'    => [
                        'post_slug'       => $post->slug,
                        'post_title'      => $post->title,
                        'author_name'     => $post->author->name,
                        'author_username' => $post->author->username,
                    ],
                ]);
            }
        }

        $this->info("Published {$posts->count()} scheduled post(s).");
    }
}
