<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Drop incorrect polymorphic columns if they exist
            if (Schema::hasColumn('comments', 'commentable_type')) {
                $table->dropColumn('commentable_type');
            }
            if (Schema::hasColumn('comments', 'commentable_id')) {
                $table->dropColumn('commentable_id');
            }
            if (Schema::hasColumn('comments', 'path')) {
                $table->dropColumn('path');
            }
            if (Schema::hasColumn('comments', 'content')) {
                $table->dropColumn('content');
            }
            if (Schema::hasColumn('comments', 'deleted')) {
                $table->dropColumn('deleted');
            }

            // Add correct columns
            if (!Schema::hasColumn('comments', 'post_id')) {
                $table->foreignId('post_id')->after('id')->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('comments', 'body')) {
                $table->text('body')->after('post_id');
            }
            if (!Schema::hasColumn('comments', 'guest_name')) {
                $table->string('guest_name')->nullable()->after('body');
            }
            if (!Schema::hasColumn('comments', 'guest_email')) {
                $table->string('guest_email')->nullable()->after('guest_name');
            }
            if (!Schema::hasColumn('comments', 'is_approved')) {
                $table->boolean('is_approved')->default(true)->after('guest_email');
            }

            // Ensure proper indexes
            if (!Schema::hasIndex('comments', 'comments_post_id_parent_id_index')) {
                $table->index(['post_id', 'parent_id']);
            }
        });
    }

    public function down(): void
    {
        // Revert is not recommended as it would lose data
    }
};
