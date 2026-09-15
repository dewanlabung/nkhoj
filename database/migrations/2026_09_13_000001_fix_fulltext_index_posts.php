<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The old single-column indexes only cover (title) and (excerpt) separately.
        // MATCH(title, excerpt) requires a composite index over both columns together.
        // Drop the old singles and replace with one composite index.

        $existing = collect(DB::select("SHOW INDEX FROM posts WHERE Key_name IN ('ft_posts_title', 'ft_posts_excerpt')"))
            ->pluck('Key_name')
            ->unique();

        if ($existing->contains('ft_posts_title')) {
            DB::statement('ALTER TABLE posts DROP INDEX ft_posts_title');
        }
        if ($existing->contains('ft_posts_excerpt')) {
            DB::statement('ALTER TABLE posts DROP INDEX ft_posts_excerpt');
        }

        DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX ft_posts_title_excerpt (title, excerpt)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE posts DROP INDEX ft_posts_title_excerpt');
        DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX ft_posts_title (title)');
        DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX ft_posts_excerpt (excerpt)');
    }
};
