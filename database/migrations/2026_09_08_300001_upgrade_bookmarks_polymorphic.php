<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Add polymorphic columns if missing (MySQL 8.0 IF NOT EXISTS)
        DB::statement('ALTER TABLE bookmarks ADD COLUMN IF NOT EXISTS bookmarkable_type VARCHAR(255) NULL AFTER user_id');
        DB::statement('ALTER TABLE bookmarks ADD COLUMN IF NOT EXISTS bookmarkable_id BIGINT UNSIGNED NULL AFTER bookmarkable_type');
        DB::statement('ALTER TABLE bookmarks ADD COLUMN IF NOT EXISTS collection VARCHAR(255) NULL AFTER bookmarkable_id');

        // 2. Migrate any un-converted rows — skip if post_id column is already gone
        try {
            DB::statement("
                UPDATE bookmarks
                SET bookmarkable_type = 'App\\\\Models\\\\Post',
                    bookmarkable_id   = post_id
                WHERE bookmarkable_type IS NULL
                  AND post_id IS NOT NULL
            ");
        } catch (\Exception $e) {
            // post_id column already dropped — nothing to migrate
        }

        // 3. Disable FK checks so we can drop the index even if the FK still references it.
        //    Note: DROP FOREIGN KEY IF EXISTS is MariaDB-only; MySQL 8 does not support it,
        //    so we bypass the constraint instead of trying to drop the FK explicitly.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 4. Drop old unique index if it still exists
        DB::statement('ALTER TABLE bookmarks DROP INDEX IF EXISTS bookmarks_user_id_post_id_unique');

        // 5. Drop post_id column if it still exists (this also drops any FK on it)
        DB::statement('ALTER TABLE bookmarks DROP COLUMN IF EXISTS post_id');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 6. Add new unique index if missing
        DB::statement('
            CREATE UNIQUE INDEX IF NOT EXISTS bookmarks_unique
            ON bookmarks (user_id, bookmarkable_type, bookmarkable_id)
        ');

        // 7. Add lookup index if missing
        DB::statement('
            CREATE INDEX IF NOT EXISTS bookmarks_bookmarkable_type_bookmarkable_id_index
            ON bookmarks (bookmarkable_type, bookmarkable_id)
        ');

        // 8. Make polymorphic columns NOT NULL now that data is migrated
        DB::statement('ALTER TABLE bookmarks MODIFY bookmarkable_type VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE bookmarks MODIFY bookmarkable_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookmarks DROP INDEX IF EXISTS bookmarks_unique');
        DB::statement('ALTER TABLE bookmarks DROP INDEX IF EXISTS bookmarks_bookmarkable_type_bookmarkable_id_index');
        DB::statement('ALTER TABLE bookmarks DROP COLUMN IF EXISTS bookmarkable_type');
        DB::statement('ALTER TABLE bookmarks DROP COLUMN IF EXISTS bookmarkable_id');
        DB::statement('ALTER TABLE bookmarks DROP COLUMN IF EXISTS collection');
    }
};
