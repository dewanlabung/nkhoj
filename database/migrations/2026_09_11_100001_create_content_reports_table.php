<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('content_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporter_id');
            $table->string('reportable_type'); // post, comment
            $table->unsignedBigInteger('reportable_id');
            $table->string('reason');          // spam, misinformation, hate_speech, violence, other
            $table->text('details')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, dismissed
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->foreign('reporter_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['reportable_type', 'reportable_id']);
            $table->index('status');
            $table->unique(['reporter_id', 'reportable_type', 'reportable_id'], 'unique_report');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_reports');
    }
};
