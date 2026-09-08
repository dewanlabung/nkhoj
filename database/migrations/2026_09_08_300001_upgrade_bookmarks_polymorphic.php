<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $columns = Schema::getColumnListing('bookmarks');

        // Add polymorphic columns only if missing
        if (!in_array('bookmarkable_type', $columns)) {
            DB::statement('ALTER TABLE bookmarks ADD COLUMN bookmarkable_type VARCHAR(255) NULL AFTER user_id');
        }
        if (!in_array('bookmarkable_id', $columns)) {
            DB::statement('ALTER TABLE bookmarks ADD COLUMN bookmarkable_id BIGINT UNSIGNED NULL AFTER bookmarkable_type');
        }

        // Migrate existing post_id records (only rows not yet migrated)
        if (in_array('post_id', $columns)) {
            DB::table('bookmarks')
                ->whereNull('bookmarkable_type')
                ->whereNotNull('post_id')
                ->update([
                    'bookmarkable_type' => 'App\\Models\\Post',
                    'bookmarkable_id'   => DB::raw('post_id'),
                ]);

            Schema::table('bookmarks', function (Blueprint $table) {
                // Must drop FK before dropping the unique index it depends on (MySQL error 1553)
                try { $table->dropForeign(['post_id']); } catch (\Exception $e) {}
                try { $table->dropUnique(['user_id', 'post_id']); } catch (\Exception $e) {}
                $table->dropColumn('post_id');
            });
        }

        Schema::table('bookmarks', function (Blueprint $table) use ($columns) {
            // Add collection column if missing
            if (!in_array('collection', $columns)) {
                $table->string('collection')->nullable()->after('bookmarkable_id');
            }

            // Add unique index if missing
            try {
                $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id'], 'bookmarks_unique');
            } catch (\Exception $e) {}

            try {
                $table->index(['bookmarkable_type', 'bookmarkable_id']);
            } catch (\Exception $e) {}
        });

        // Make polymorphic columns NOT NULL now that data is migrated
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
            try { $table->dropIndex('bookmarks_unique'); } catch (\Exception $e) {}
            try { $table->dropIndex(['bookmarkable_type', 'bookmarkable_id']); } catch (\Exception $e) {}
            $table->dropColumn(['bookmarkable_type', 'bookmarkable_id', 'collection']);
            try { $table->unique(['user_id', 'post_id']); } catch (\Exception $e) {}
        });
    }
};
