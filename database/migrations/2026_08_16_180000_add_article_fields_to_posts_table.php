<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('optional_url', 500)->nullable()->after('event_faq');
            $table->json('sources')->nullable()->after('optional_url');
            $table->json('article_faq')->nullable()->after('sources');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['optional_url', 'sources', 'article_faq']);
        });
    }
};
