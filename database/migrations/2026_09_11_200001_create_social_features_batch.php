<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ─── Feature 1: Live Streaming ────────────────────────────────────────────
        Schema::create('live_streams', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('embed_url')->nullable();  // YouTube/FB Live embed URL
            $table->string('thumbnail_url')->nullable();
            $table->enum('status', ['live', 'ended'])->default('live');
            $table->unsignedInteger('viewer_count')->default(0);
            $table->unsignedInteger('peak_viewers')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('live_stream_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_stream_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->text('body');
            $table->timestamps();
        });

        // ─── Feature 2: Reels ─────────────────────────────────────────────────────
        Schema::create('reels', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('video_url');              // local /uploads/reels/... or external
            $table->string('thumbnail_url')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('reel_likes', function (Blueprint $table) {
            $table->foreignId('reel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['reel_id', 'user_id']);
        });

        Schema::create('reel_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        // ─── Feature 3: Watch Parties ─────────────────────────────────────────────
        Schema::create('watch_parties', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('video_url');
            $table->enum('status', ['waiting', 'playing', 'paused', 'ended'])->default('waiting');
            $table->string('join_code', 12)->unique();
            $table->unsignedInteger('playback_seconds')->default(0);
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('watch_party_members', function (Blueprint $table) {
            $table->foreignId('watch_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->primary(['watch_party_id', 'user_id']);
        });

        Schema::create('watch_party_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watch_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->unsignedInteger('at_seconds')->default(0);
            $table->timestamps();
        });

        // ─── Feature 4: Virtual Gifts ─────────────────────────────────────────────
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('coins')->default(0);
            $table->unsignedInteger('total_earned')->default(0);
            $table->unsignedInteger('total_spent')->default(0);
            $table->timestamps();
        });

        Schema::create('virtual_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('gift_type', 30);          // rose, star, crown, trophy, diamond
            $table->unsignedInteger('coins');
            $table->string('context_type')->nullable(); // live_stream, reel, post, profile
            $table->unsignedBigInteger('context_id')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });

        // ─── Feature 5: Disappearing DMs ─────────────────────────────────────────
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->timestamps();
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->primary(['conversation_id', 'user_id']);
        });

        Schema::create('direct_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('media_url')->nullable();
            $table->boolean('is_view_once')->default(false);
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();     // null = permanent
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direct_messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('virtual_gifts');
        Schema::dropIfExists('user_wallets');
        Schema::dropIfExists('watch_party_messages');
        Schema::dropIfExists('watch_party_members');
        Schema::dropIfExists('watch_parties');
        Schema::dropIfExists('reel_comments');
        Schema::dropIfExists('reel_likes');
        Schema::dropIfExists('reels');
        Schema::dropIfExists('live_stream_messages');
        Schema::dropIfExists('live_streams');
    }
};
