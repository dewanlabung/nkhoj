<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add announcement + new fields to social_pages
        Schema::table('social_pages', function (Blueprint $table) {
            $table->text('announcement')->nullable()->after('bio');
            $table->json('highlights')->nullable()->after('announcement'); // [{title, url, icon}]
        });

        // Add post format fields to page_posts
        Schema::table('page_posts', function (Blueprint $table) {
            $table->string('post_format', 20)->default('standard')->after('type'); // standard|article|poll|qna
            $table->string('article_title', 255)->nullable()->after('post_format');
        });

        // Poll options
        Schema::create('page_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_post_id')->constrained()->cascadeOnDelete();
            $table->string('text', 200);
            $table->unsignedInteger('votes_count')->default(0);
            $table->unsignedTinyInteger('sort_order')->default(0);
        });

        // Poll votes
        Schema::create('page_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('page_poll_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['page_post_id', 'user_id']);
        });

        // Q&A on page
        Schema::create('page_qna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('question', 500);
            $table->text('answer')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        // Products / Services catalog
        Schema::create('page_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 10)->default('NPR');
            $table->string('image_url')->nullable();
            $table->string('link_url')->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_products');
        Schema::dropIfExists('page_qna');
        Schema::dropIfExists('page_poll_votes');
        Schema::dropIfExists('page_poll_options');
        Schema::table('page_posts', function (Blueprint $table) {
            $table->dropColumn(['post_format', 'article_title']);
        });
        Schema::table('social_pages', function (Blueprint $table) {
            $table->dropColumn(['announcement', 'highlights']);
        });
    }
};
