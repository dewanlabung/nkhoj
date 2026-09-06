<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);          // popular_posts, popular_tags, voting_poll, recommended_posts, follow_us, newsletter, about_us
            $table->string('title', 150);         // display title
            $table->string('where_to_display', 80); // sidebar, latest_posts, home_top, home_bottom, category_page, post_sidebar, footer
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();   // extra options per type
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
