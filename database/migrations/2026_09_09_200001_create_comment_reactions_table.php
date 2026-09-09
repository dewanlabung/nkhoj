<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('session_key', 64)->nullable();
            $table->enum('emoji', ['👍', '❤️', '😂', '😮', '😢', '😡'])->default('👍');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['comment_id', 'user_id']);
            $table->index(['comment_id', 'emoji']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
