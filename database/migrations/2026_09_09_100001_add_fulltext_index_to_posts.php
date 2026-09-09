<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX ft_posts_title (title)');
        DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX ft_posts_excerpt (excerpt)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE posts DROP INDEX ft_posts_title');
        DB::statement('ALTER TABLE posts DROP INDEX ft_posts_excerpt');
    }
};
