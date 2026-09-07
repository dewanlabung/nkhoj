<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('stripe_session_id');
        });

        // Extend the status enum to include pending_manual
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active','expired','cancelled','pending','pending_manual') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active','expired','cancelled','pending') DEFAULT 'pending'");
    }
};
