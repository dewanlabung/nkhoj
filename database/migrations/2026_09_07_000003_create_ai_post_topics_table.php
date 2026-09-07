<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_post_topics', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->string('language')->default('both'); // 'en', 'ne', 'both'
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('rss_source')->nullable(); // URL of RSS feed to use as context
            $table->enum('frequency', ['daily', 'weekly', 'manual'])->default('daily');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });

        // Store Gemini API key and AI settings in site_settings (no separate table needed)
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_post_topics');
    }
};
