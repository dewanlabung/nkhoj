<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('session_key', 64)->nullable();
            $table->tinyInteger('vote'); // 1 = up, -1 = down
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['question_id', 'user_id']);
        });

        Schema::create('answer_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('session_key', 64)->nullable();
            $table->tinyInteger('vote'); // 1 = up, -1 = down
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['answer_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answer_votes');
        Schema::dropIfExists('question_votes');
    }
};
