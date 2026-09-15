<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['text', 'photo', 'video', 'event'])->default('text');
            $table->text('body')->nullable();
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('event_title')->nullable();
            $table->timestamp('event_start')->nullable();
            $table->timestamp('event_end')->nullable();
            $table->string('event_venue')->nullable();
            $table->string('event_ticket_url')->nullable();
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->timestamps();
        });

        Schema::create('page_post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['page_post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_post_likes');
        Schema::dropIfExists('page_posts');
    }
};
