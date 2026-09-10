<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_page_id')->constrained('social_pages')->cascadeOnDelete();
            $table->morphs('reportable'); // reportable_type + reportable_id
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('reason', ['spam', 'inappropriate', 'harassment', 'fake', 'other']);
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'dismissed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['reportable_type', 'reportable_id', 'user_id'], 'unique_report_per_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_reports');
    }
};
