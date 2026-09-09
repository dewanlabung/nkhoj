<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('series_id')->nullable()->after('category_id')
                  ->constrained('post_series')->nullOnDelete();
            $table->unsignedSmallInteger('series_order')->nullable()->after('series_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\PostSeries::class, 'series_id');
            $table->dropColumn(['series_id', 'series_order']);
        });
        Schema::dropIfExists('post_series');
    }
};
