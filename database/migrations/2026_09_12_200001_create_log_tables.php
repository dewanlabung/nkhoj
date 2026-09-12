<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('schedule_log')) {
            Schema::create('schedule_log', function (Blueprint $table) {
                $table->id();
                $table->string('command');
                $table->text('output')->nullable();
                $table->integer('exit_code')->default(0);
                $table->integer('duration')->default(0); // ms
                $table->timestamp('ran_at')->nullable();
                $table->integer('count_in_last_hour')->default(1);
            });
        }

        if (!Schema::hasTable('outgoing_email_log')) {
            Schema::create('outgoing_email_log', function (Blueprint $table) {
                $table->id();
                $table->string('message_id')->nullable();
                $table->string('from')->nullable();
                $table->string('to');
                $table->string('subject')->nullable();
                $table->longText('mime')->nullable();
                $table->enum('status', ['not-sent', 'sent'])->default('not-sent');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_log');
        Schema::dropIfExists('outgoing_email_log');
    }
};
