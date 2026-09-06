<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 80)->nullable()->after('name');
            $table->string('last_name', 80)->nullable()->after('first_name');
            $table->decimal('balance', 10, 2)->default(0)->after('last_name');
            $table->boolean('reward_system')->default(false)->after('balance');
            $table->unsignedBigInteger('profile_view_count')->default(0)->after('reward_system');
            $table->json('extra_permissions')->nullable()->after('profile_view_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name','last_name','balance','reward_system','profile_view_count','extra_permissions']);
        });
    }
};
