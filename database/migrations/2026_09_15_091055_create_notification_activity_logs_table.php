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
        if (!Schema::hasTable('notification_activity_logs')) {
            Schema::create('notification_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('notif_type'); // 'comment_replied', 'system_error', etc.
            $table->string('action'); // 'sent', 'read', 'deleted', 'delivery_failed'
            $table->string('channel')->nullable(); // 'database', 'email', 'fcm', 'broadcast'
            $table->uuid('notification_id')->nullable(); // Reference to notification
            $table->json('data')->nullable(); // Additional tracking data
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['notif_type', 'action']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_activity_logs');
    }
};
