<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Users ────────────────────────────────────────
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('username', 50)->unique();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('avatar_url')->nullable();
            $table->enum('role', ['reader', 'author', 'editor', 'admin'])->default('reader');
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // ── Categories ───────────────────────────────────
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('slug', 100)->unique();
            $table->string('name_en', 100);
            $table->string('name_ne', 100)->nullable();
            $table->string('meta_title', 160)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Tags ─────────────────────────────────────────
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('name_en', 100);
            $table->string('name_ne', 100)->nullable();
            $table->timestamps();
        });

        // ── Posts ─────────────────────────────────────────
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('slug', 200)->unique();
            $table->string('title', 300);
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->string('thumbnail_url')->nullable();
            $table->string('seo_title', 160)->nullable();
            $table->string('seo_desc', 320)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index(['published_at', 'status']);
            $table->index('is_featured');
        });

        // ── Post ↔ Tag pivot ──────────────────────────────
        Schema::create('post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'tag_id']);
        });

        // ── Surveys ───────────────────────────────────────
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('title', 300);
            $table->enum('type', ['poll', 'quiz', 'nps', 'rating', 'form'])->default('poll');
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('sort_order')->default(0);
            $table->text('question');
            $table->enum('type', ['single', 'multiple', 'text', 'rating', 'nps'])->default('single');
            $table->timestamps();
        });

        Schema::create('survey_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_question_id')->constrained('survey_questions')->cascadeOnDelete();
            $table->string('label', 300);
            $table->smallInteger('sort_order')->default(0);
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('session_id', 64)->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['survey_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_options');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
    }
};
