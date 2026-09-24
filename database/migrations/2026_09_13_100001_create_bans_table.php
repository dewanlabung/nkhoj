<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bans')) {
            Schema::create('bans', function (Blueprint $table) {
            $table->id();
            $table->morphs('bannable');               // bannable_type, bannable_id (User, etc.)
            $table->string('comment')->nullable();
            $table->timestamp('expired_at')->nullable(); // NULL = permanent
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->timestamps();

            $table->index('expired_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bans');
    }
};
