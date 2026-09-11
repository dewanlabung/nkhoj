<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Stories ──────────────────────────────────────────
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('media_url');
            $table->string('media_type')->default('image'); // image | video
            $table->string('caption', 500)->nullable();
            $table->timestamp('expires_at'); // 24h from creation by default
            $table->timestamps();
            $table->index(['user_id', 'expires_at']);
        });

        // ── Story Highlights ──────────────────────────────────
        Schema::create('story_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->string('cover_url')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('story_highlight_items', function (Blueprint $table) {
            $table->foreignId('highlight_id')->constrained('story_highlights')->cascadeOnDelete();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->default(0);
            $table->primary(['highlight_id', 'story_id']);
        });

        // ── Broadcast Channels ───────────────────────────────
        Schema::create('broadcast_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->string('description', 500)->nullable();
            $table->string('cover_url')->nullable();
            $table->unsignedBigInteger('subscriber_count')->default(0);
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('channel_subscribers', function (Blueprint $table) {
            $table->foreignId('channel_id')->constrained('broadcast_channels')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('subscribed_at')->useCurrent();
            $table->primary(['channel_id', 'user_id']);
        });

        Schema::create('channel_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('broadcast_channels')->cascadeOnDelete();
            $table->text('body');
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->unsignedBigInteger('reactions_count')->default(0);
            $table->timestamps();
            $table->index(['channel_id', 'created_at']);
        });

        Schema::create('channel_message_reactions', function (Blueprint $table) {
            $table->foreignId('channel_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('emoji', 10)->default('👍');
            $table->primary(['channel_message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_message_reactions');
        Schema::dropIfExists('channel_messages');
        Schema::dropIfExists('channel_subscribers');
        Schema::dropIfExists('broadcast_channels');
        Schema::dropIfExists('story_highlight_items');
        Schema::dropIfExists('story_highlights');
        Schema::dropIfExists('stories');
    }
};
