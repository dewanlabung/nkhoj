<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_analytics', function (Blueprint $table) {
            $table->date('date');
            $table->unsignedBigInteger('page_views')->default(0);
            $table->unsignedBigInteger('unique_visitors')->default(0);
            $table->primary('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_analytics');
    }
};
