<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('social_pages', function (Blueprint $table) {
            $table->enum('status', ['active', 'disabled', 'suspended'])->default('active')->after('is_active');
            $table->text('disabled_reason')->nullable()->after('status');
            $table->foreignId('pinned_post_id')->nullable()->after('disabled_reason')
                  ->constrained('page_posts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('social_pages', function (Blueprint $table) {
            $table->dropForeign(['pinned_post_id']);
            $table->dropColumn(['status', 'disabled_reason', 'pinned_post_id']);
        });
    }
};
