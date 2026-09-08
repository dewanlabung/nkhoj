<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Migrate existing post bookmarks to polymorphic format
        DB::statement('ALTER TABLE bookmarks ADD COLUMN bookmarkable_type VARCHAR(255) NULL AFTER user_id');
        DB::statement('ALTER TABLE bookmarks ADD COLUMN bookmarkable_id BIGINT UNSIGNED NULL AFTER bookmarkable_type');

        // Convert existing post_id records
        DB::table('bookmarks')->update([
            'bookmarkable_type' => 'App\\Models\\Post',
            'bookmarkable_id'   => DB::raw('post_id'),
        ]);

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'post_id']);
            $table->dropColumn('post_id');
            $table->string('collection')->nullable()->after('bookmarkable_id'); // future: custom lists
            $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id'], 'bookmarks_unique');
            $table->index(['bookmarkable_type', 'bookmarkable_id']);
        });

        // Make polymorphic columns not nullable now that data is migrated
        DB::statement('ALTER TABLE bookmarks MODIFY bookmarkable_type VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE bookmarks MODIFY bookmarkable_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->foreignId('post_id')->nullable()->constrained()->cascadeOnDelete();
        });

        DB::table('bookmarks')
            ->where('bookmarkable_type', 'App\\Models\\Post')
            ->update(['post_id' => DB::raw('bookmarkable_id')]);

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropIndex('bookmarks_unique');
            $table->dropIndex(['bookmarkable_type', 'bookmarkable_id']);
            $table->dropColumn(['bookmarkable_type', 'bookmarkable_id', 'collection']);
            $table->unique(['user_id', 'post_id']);
        });
    }
};
