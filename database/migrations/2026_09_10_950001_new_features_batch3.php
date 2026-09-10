<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add batch-3 columns to social_pages
        Schema::table('social_pages', function (Blueprint $table) {
            $table->string('page_type', 30)->default('other')->after('username');
            $table->string('posts_privacy_default', 20)->default('public')->after('allow_tagging');
            $table->boolean('allow_recommendations')->default(true)->after('posts_privacy_default');
            $table->timestamp('last_post_at')->nullable()->after('allow_recommendations');
        });

        // FAQ / pinned questions on a page (admin-curated)
        Schema::create('page_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained('social_pages')->cascadeOnDelete();
            $table->string('question', 300);
            $table->text('answer');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
            $table->index(['social_page_id', 'display_order']);
        });

        // Milestones (auto-generated at follower thresholds)
        Schema::create('page_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained('social_pages')->cascadeOnDelete();
            $table->string('milestone_type', 40); // followers, years, posts
            $table->unsignedBigInteger('milestone_value');
            $table->timestamp('achieved_at');
            $table->unique(['social_page_id', 'milestone_type', 'milestone_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_milestones');
        Schema::dropIfExists('page_faqs');
        Schema::table('social_pages', function (Blueprint $table) {
            $table->dropColumn(['page_type', 'posts_privacy_default', 'allow_recommendations', 'last_post_at']);
        });
    }
};
