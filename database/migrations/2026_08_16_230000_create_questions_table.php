<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('featured_image')->nullable();
            $table->boolean('is_poll')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_private')->default(false);
            $table->boolean('notify_email')->default(true);
            $table->enum('status', ['open', 'closed', 'pending'])->default('open');
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('answers_count')->default(0);
            $table->unsignedBigInteger('best_answer_id')->nullable();
            $table->timestamps();
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('content');
            $table->boolean('is_best')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->integer('votes')->default(0);
            $table->timestamps();
        });

        Schema::create('question_tag', function (Blueprint $table) {
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['question_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_tag');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
    }
};
