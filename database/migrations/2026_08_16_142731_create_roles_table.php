<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('name_ne', 100)->nullable();
            $table->string('slug', 80)->unique();
            $table->string('badge_label', 80)->nullable();
            $table->string('badge_color', 30)->default('gray');
            $table->string('badge_icon', 30)->default('user');
            $table->json('permissions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('ai_credits')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('ai_credits_used')->default(0)->after('role');
            $table->timestamp('ai_credits_reset_at')->nullable()->after('ai_credits_used');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ai_credits_used', 'ai_credits_reset_at']);
        });
    }
};
