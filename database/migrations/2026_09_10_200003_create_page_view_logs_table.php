<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_view_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('views')->default(0);
            $table->unique(['social_page_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_view_logs');
    }
};
