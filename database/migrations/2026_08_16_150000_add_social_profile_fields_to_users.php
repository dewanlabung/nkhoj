<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('social_links')->nullable()->after('website');
            $table->timestamp('last_seen_at')->nullable()->after('social_links');
            $table->string('cover_url', 500)->nullable()->after('avatar_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['social_links', 'last_seen_at', 'cover_url']);
        });
    }
};
