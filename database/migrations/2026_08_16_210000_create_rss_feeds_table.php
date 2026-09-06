<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rss_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('language', 10)->default('en');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('post_count')->default(1);
            $table->boolean('auto_update')->default(true);
            $table->boolean('show_read_more')->default(true);
            $table->boolean('add_as_draft')->default(false);
            $table->boolean('generate_keywords')->default(false);
            $table->string('read_more_text')->default('Read More');
            $table->string('default_image')->nullable();
            $table->string('images_source', 30)->default('original');
            $table->integer('imported_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_feeds');
    }
};
