<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('notif_id')->index(); // 'comment_replied', 'post_commented'
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('channels')->default('{}'); // {browser: true, email: false, mobile: true}
            $table->timestamps();

            $table->unique(['user_id', 'notif_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_subscriptions');
    }
};
