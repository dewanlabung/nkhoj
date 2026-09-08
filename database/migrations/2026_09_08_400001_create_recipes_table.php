<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cuisine_type', 100)->nullable(); // Nepali, Indian, Chinese, Italian...
            $table->string('meal_type', 50)->nullable();     // Breakfast, Lunch, Dinner, Snack, Dessert
            $table->string('difficulty', 20)->default('easy'); // easy, medium, hard
            $table->integer('prep_time')->nullable();   // minutes
            $table->integer('cook_time')->nullable();   // minutes
            $table->integer('servings')->default(2);
            $table->json('ingredients')->nullable();
            $table->json('steps')->nullable();
            $table->json('tags')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->index(['cuisine_type', 'is_published']);
            $table->index(['meal_type', 'is_published']);
        });

        Schema::create('recipe_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['recipe_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_likes');
        Schema::dropIfExists('recipes');
    }
};
