<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained('social_pages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['admin', 'moderator', 'editor'])->default('admin');
            $table->timestamp('accepted_at')->nullable(); // null = pending invite
            $table->timestamps();

            $table->unique(['social_page_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_admins');
    }
};
