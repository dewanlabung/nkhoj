<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private function fkExists(string $table, string $fkName): bool
    {
        $rows = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$table, $fkName]
        );
        return ($rows[0]->cnt ?? 0) > 0;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($rows) > 0;
    }

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

            // Drop FK via raw SQL only if it still exists
            if ($this->fkExists('bookmarks', 'bookmarks_post_id_foreign')) {
                DB::statement('ALTER TABLE bookmarks DROP FOREIGN KEY bookmarks_post_id_foreign');
            }

            // Drop unique index via raw SQL only if it still exists
            if ($this->indexExists('bookmarks', 'bookmarks_user_id_post_id_unique')) {
                DB::statement('ALTER TABLE bookmarks DROP INDEX bookmarks_user_id_post_id_unique');
            }

            Schema::table('bookmarks', function (Blueprint $table) {
                $table->dropColumn('post_id');
            });
        }

        // Add collection column if missing
        if (!in_array('collection', Schema::getColumnListing('bookmarks'))) {
            Schema::table('bookmarks', function (Blueprint $table) {
                $table->string('collection')->nullable()->after('bookmarkable_id');
            });
        }

        // Add new unique index if missing
        if (!$this->indexExists('bookmarks', 'bookmarks_unique')) {
            Schema::table('bookmarks', function (Blueprint $table) {
                $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id'], 'bookmarks_unique');
            });
        }

        // Add lookup index if missing
        if (!$this->indexExists('bookmarks', 'bookmarks_bookmarkable_type_bookmarkable_id_index')) {
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

        if ($this->indexExists('bookmarks', 'bookmarks_unique')) {
            DB::statement('ALTER TABLE bookmarks DROP INDEX bookmarks_unique');
        }
        if ($this->indexExists('bookmarks', 'bookmarks_bookmarkable_type_bookmarkable_id_index')) {
            DB::statement('ALTER TABLE bookmarks DROP INDEX bookmarks_bookmarkable_type_bookmarkable_id_index');
        }

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropColumn(['bookmarkable_type', 'bookmarkable_id', 'collection']);
        });

        if (!$this->indexExists('bookmarks', 'bookmarks_user_id_post_id_unique')) {
            Schema::table('bookmarks', function (Blueprint $table) {
                $table->unique(['user_id', 'post_id']);
            });
        }
    }
};
