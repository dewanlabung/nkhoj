<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_post_likes', function (Blueprint $table) {
            $table->string('reaction', 20)->default('like')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('page_post_likes', function (Blueprint $table) {
            $table->dropColumn('reaction');
        });
    }
};
