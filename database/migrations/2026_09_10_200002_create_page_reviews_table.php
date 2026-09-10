<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating');
            $table->text('body')->nullable();
            $table->timestamps();
            $table->unique(['social_page_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_reviews');
    }
};
