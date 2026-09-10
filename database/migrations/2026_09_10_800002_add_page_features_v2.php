<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Action button + privacy settings on social_pages
        Schema::table('social_pages', function (Blueprint $table) {
            $table->string('action_button_type', 30)->nullable()->after('highlights');
            $table->string('action_button_text', 60)->nullable()->after('action_button_type');
            $table->string('action_button_url')->nullable()->after('action_button_text');
            $table->boolean('allow_tagging')->default(true)->after('action_button_url');
            $table->boolean('is_archived')->default(false)->after('allow_tagging');
        });

        // Block users from interacting with a page
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained('social_pages')->cascadeOnDelete();
            $table->foreignId('blocked_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['social_page_id', 'blocked_user_id']);
        });

        // Activity log for owner/admin audit trail
        Schema::create('page_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained('social_pages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['social_page_id', 'created_at']);
        });

        // Per-follower notification preferences
        Schema::create('page_notification_prefs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('social_page_id')->constrained('social_pages')->cascadeOnDelete();
            $table->boolean('notify_posts')->default(true);
            $table->boolean('notify_events')->default(true);
            $table->boolean('notify_announcements')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'social_page_id']);
        });

        // Stories (24-hour ephemeral content)
        Schema::create('page_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained('social_pages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('image_url')->nullable();
            $table->string('caption', 255)->nullable();
            $table->string('bg_color', 20)->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['social_page_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_stories');
        Schema::dropIfExists('page_notification_prefs');
        Schema::dropIfExists('page_activity_logs');
        Schema::dropIfExists('page_blocks');
        Schema::table('social_pages', function (Blueprint $table) {
            $table->dropColumn(['action_button_type', 'action_button_text', 'action_button_url', 'allow_tagging', 'is_archived']);
        });
    }
};
