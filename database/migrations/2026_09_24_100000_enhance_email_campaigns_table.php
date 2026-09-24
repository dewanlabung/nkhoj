<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_campaigns')) {
            Schema::table('email_campaigns', function (Blueprint $table) {
                if (!Schema::hasColumn('email_campaigns', 'recipient_filter')) {
                    $table->enum('recipient_filter', [
                        'all', 'activated', 'inactive', 'week', 'month', '3months',
                        '6months', '9months', 'year', 'newsletter'
                    ])->default('newsletter')->after('status');
                }

                if (!Schema::hasColumn('email_campaigns', 'sent_at')) {
                    $table->timestamp('sent_at')->nullable()->after('updated_at');
                }

                if (!Schema::hasColumn('email_campaigns', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('sent_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('email_campaigns')) {
            Schema::table('email_campaigns', function (Blueprint $table) {
                $table->dropColumn(['recipient_filter', 'sent_at', 'completed_at']);
            });
        }
    }
};
