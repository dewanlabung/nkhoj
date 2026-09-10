<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // events
        DB::statement('ALTER TABLE events ADD FULLTEXT INDEX ft_events_title_desc (title, description)');

        // social_pages
        DB::statement('ALTER TABLE social_pages ADD FULLTEXT INDEX ft_pages_name_bio (name, bio)');

        // questions
        DB::statement('ALTER TABLE questions ADD FULLTEXT INDEX ft_questions_title_content (title, content)');

        // recipes
        DB::statement('ALTER TABLE recipes ADD FULLTEXT INDEX ft_recipes_title_desc (title, description)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE events DROP INDEX ft_events_title_desc');
        DB::statement('ALTER TABLE social_pages DROP INDEX ft_pages_name_bio');
        DB::statement('ALTER TABLE questions DROP INDEX ft_questions_title_content');
        DB::statement('ALTER TABLE recipes DROP INDEX ft_recipes_title_desc');
    }
};
