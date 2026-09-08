<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable(); // Music, Sports, Tech, Food, Art...
            $table->string('event_type', 50)->default('in-person'); // in-person, online, hybrid
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('venue')->nullable();
            $table->string('location')->nullable();
            $table->string('organizer')->nullable();
            $table->string('registration_url')->nullable();
            $table->decimal('ticket_price', 10, 2)->nullable(); // null = free
            $table->string('thumbnail_url')->nullable();
            $table->integer('interested_count')->default(0);
            $table->integer('going_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->index(['starts_at', 'is_published']);
            $table->index(['category', 'is_published']);
        });

        Schema::create('event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('interested'); // interested, going
            $table->unique(['event_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendees');
        Schema::dropIfExists('events');
    }
};
