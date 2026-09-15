<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('commentable_type'); // Post, Article, etc.
            $table->unsignedBigInteger('commentable_id');
            $table->longText('content');
            $table->string('path')->nullable()->index(); // '1/2/3' for nesting
            $table->boolean('deleted')->default(false)->index();
            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['commentable_type', 'commentable_id']);
            $table->index('user_id');
            $table->fullText(['content']); // For full-text search
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
