<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('social_pages', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('location');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->json('business_hours')->nullable()->after('lng');
            $table->decimal('rating_avg', 3, 2)->default(0)->after('business_hours');
            $table->unsignedInteger('reviews_count')->default(0)->after('rating_avg');
            $table->unsignedBigInteger('views_count')->default(0)->after('reviews_count');
        });
    }

    public function down(): void
    {
        Schema::table('social_pages', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'business_hours', 'rating_avg', 'reviews_count', 'views_count']);
        });
    }
};
