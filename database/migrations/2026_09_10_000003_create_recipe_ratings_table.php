<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating'); // 1-5
            $table->text('review')->nullable();
            $table->timestamps();
            $table->unique(['recipe_id', 'user_id']);
        });

        // Add cached rating columns to recipes
        Schema::table('recipes', function (Blueprint $table) {
            $table->decimal('rating_avg', 3, 2)->default(0)->after('is_featured');
            $table->unsignedInteger('ratings_count')->default(0)->after('rating_avg');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ratings');
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['rating_avg', 'ratings_count']);
        });
    }
};
