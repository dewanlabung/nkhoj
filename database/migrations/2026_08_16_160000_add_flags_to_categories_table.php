<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->boolean('is_exclusive')->default(false)->after('is_active');
            $table->string('color', 20)->nullable()->after('is_exclusive');
            $table->string('icon', 50)->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'is_exclusive', 'color', 'icon']);
        });
    }
};
