<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'recovery_email')) {
                $table->string('recovery_email')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'recovery_email_verified_at')) {
                $table->timestamp('recovery_email_verified_at')->nullable()->after('recovery_email');
            }
            if (!Schema::hasColumn('users', 'recovery_token')) {
                $table->string('recovery_token', 64)->nullable()->after('recovery_email_verified_at');
            }
            if (!Schema::hasColumn('users', 'recovery_token_expires_at')) {
                $table->timestamp('recovery_token_expires_at')->nullable()->after('recovery_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['recovery_email', 'recovery_email_verified_at', 'recovery_token', 'recovery_token_expires_at']);
        });
    }
};
