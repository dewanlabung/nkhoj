<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->longText('html_content');
            $table->longText('text_content')->nullable();
            $table->unsignedBigInteger('total_recipients')->default(0);
            $table->unsignedBigInteger('sent_count')->default(0);
            $table->enum('status', ['draft', 'sending', 'completed', 'failed'])->default('draft');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
