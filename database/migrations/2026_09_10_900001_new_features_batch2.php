<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Search logs for trending searches
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('query', 255);
            $table->unsignedSmallInteger('results_count')->default(0);
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['query', 'created_at']);
        });

        // Sensitive content flag + repost + promoted on posts
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_sensitive')->default(false)->after('status');
            $table->boolean('is_promoted')->default(false)->after('is_sensitive');
            $table->timestamp('promoted_until')->nullable()->after('is_promoted');
            $table->foreignId('repost_of_id')->nullable()->constrained('posts')->nullOnDelete()->after('promoted_until');
        });

        // Donation config on social pages
        Schema::table('social_pages', function (Blueprint $table) {
            $table->string('donation_url', 300)->nullable()->after('action_button_url');
            $table->string('donation_label', 60)->nullable()->after('donation_url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['is_sensitive', 'is_promoted', 'promoted_until', 'repost_of_id']);
        });
        Schema::table('social_pages', function (Blueprint $table) {
            $table->dropColumn(['donation_url', 'donation_label']);
        });
    }
};
