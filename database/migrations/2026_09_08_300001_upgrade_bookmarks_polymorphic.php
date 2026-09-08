<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Add polymorphic columns only if not already present (idempotent)
        if (!Schema::hasColumn('bookmarks', 'bookmarkable_type')) {
            DB::statement('ALTER TABLE bookmarks ADD COLUMN bookmarkable_type VARCHAR(255) NULL AFTER user_id');
        }
        if (!Schema::hasColumn('bookmarks', 'bookmarkable_id')) {
            DB::statement('ALTER TABLE bookmarks ADD COLUMN bookmarkable_id BIGINT UNSIGNED NULL AFTER bookmarkable_type');
        }

        // Convert existing post_id records (safe to re-run — already-converted rows won't change)
        if (Schema::hasColumn('bookmarks', 'post_id')) {
            DB::table('bookmarks')
                ->whereNull('bookmarkable_type')
                ->update([
                    'bookmarkable_type' => 'App\\Models\\Post',
                    'bookmarkable_id'   => DB::raw('post_id'),
                ]);

            Schema::table('bookmarks', function (Blueprint $table) {
                // Must drop FK before dropping the unique index it depends on
                $table->dropForeign(['post_id']);
                $table->dropUnique(['user_id', 'post_id']);
                $table->dropColumn('post_id');
            });
        }

        if (!Schema::hasColumn('bookmarks', 'collection')) {
            Schema::table('bookmarks', function (Blueprint $table) {
                $table->string('collection')->nullable()->after('bookmarkable_id');
            });
        }

        // Add new unique + index if they don't already exist
        $indexes = collect(DB::select("SHOW INDEX FROM bookmarks"))->pluck('Key_name')->unique();
        if (!$indexes->contains('bookmarks_unique')) {
            Schema::table('bookmarks', function (Blueprint $table) {
                $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id'], 'bookmarks_unique');
            });
        }
        if (!$indexes->contains('bookmarks_bookmarkable_type_bookmarkable_id_index')) {
            Schema::table('bookmarks', function (Blueprint $table) {
                $table->index(['bookmarkable_type', 'bookmarkable_id']);
            });
        }

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
            $table->dropIndex('bookmarks_unique');
            $table->dropIndex(['bookmarkable_type', 'bookmarkable_id']);
            $table->dropColumn(['bookmarkable_type', 'bookmarkable_id', 'collection']);
            $table->unique(['user_id', 'post_id']);
        });
    }
};
