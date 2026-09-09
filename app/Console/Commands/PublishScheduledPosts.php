<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PublishScheduledPosts extends Command
{
    protected $signature   = 'posts:publish-scheduled';
    protected $description = 'Publish posts whose scheduled_at has passed';

    public function handle(): void
    {
        $count = Post::where('status', 'scheduled')
            ->where('scheduled_at', '<=', Carbon::now())
            ->update([
                'status'       => 'published',
                'published_at' => Carbon::now(),
            ]);

        $this->info("Published {$count} scheduled post(s).");
    }
}
