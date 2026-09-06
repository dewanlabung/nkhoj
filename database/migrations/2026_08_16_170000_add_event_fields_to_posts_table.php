<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('event_start_at')->nullable()->after('scheduled_at');
            $table->timestamp('event_end_at')->nullable()->after('event_start_at');
            $table->string('event_organizer', 200)->nullable()->after('event_end_at');
            $table->string('event_venue', 200)->nullable()->after('event_organizer');
            $table->text('event_address')->nullable()->after('event_venue');
            $table->decimal('event_lat', 10, 6)->nullable()->after('event_address');
            $table->decimal('event_lng', 10, 6)->nullable()->after('event_lat');
            $table->json('event_schedule')->nullable()->after('event_lng');
            $table->json('event_highlights')->nullable()->after('event_schedule');
            $table->json('event_speakers')->nullable()->after('event_highlights');
            $table->string('event_registration_type', 50)->default('none')->after('event_speakers');
            $table->json('event_faq')->nullable()->after('event_registration_type');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'event_start_at','event_end_at','event_organizer','event_venue',
                'event_address','event_lat','event_lng','event_schedule',
                'event_highlights','event_speakers','event_registration_type','event_faq',
            ]);
        });
    }
};
