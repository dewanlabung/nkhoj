<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('social_pages', function (Blueprint $table) {
            $table->string('username', 60)->nullable()->unique()->after('slug');
            $table->json('social_links')->nullable()->after('website');
        });

        // Fix pages with NULL status (created before the status column existed)
        DB::table('social_pages')->whereNull('status')->update(['status' => 'active']);
    }

    public function down(): void
    {
        Schema::table('social_pages', function (Blueprint $table) {
            $table->dropColumn(['username', 'social_links']);
        });
    }
};
