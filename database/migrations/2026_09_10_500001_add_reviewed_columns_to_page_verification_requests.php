<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_verification_requests', function (Blueprint $table) {
            $table->timestamp('reviewed_at')->nullable()->after('status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            $table->text('admin_notes')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('page_verification_requests', function (Blueprint $table) {
            $table->dropColumn(['reviewed_at', 'reviewed_by', 'admin_notes']);
        });
    }
};
