<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_post_id')->constrained('page_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::table('page_posts', function (Blueprint $table) {
            $table->unsignedInteger('comments_count')->default(0)->after('likes_count');
        });
    }

    public function down(): void
    {
        Schema::table('page_posts', function (Blueprint $table) {
            $table->dropColumn('comments_count');
        });
        Schema::dropIfExists('page_post_comments');
    }
};
