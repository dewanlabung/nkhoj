<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Personal Access Tokens — already handled by Sanctum's personal_access_tokens table
        // We add a last_used_ip column via Sanctum's existing table if not present
        if (Schema::hasTable('personal_access_tokens') && !Schema::hasColumn('personal_access_tokens', 'last_used_ip')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->string('last_used_ip', 45)->nullable()->after('last_used_at');
            });
        }

        // Active sessions tracker
        if (!Schema::hasTable('user_sessions')) {
            Schema::create('user_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('session_id', 128)->unique();
                $table->string('ip', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('device_type', 20)->nullable(); // desktop, mobile, tablet
                $table->string('country', 80)->nullable();
                $table->string('city', 80)->nullable();
                $table->timestamp('last_active_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // API request audit log
        if (!Schema::hasTable('api_audit_logs')) {
            Schema::create('api_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('token_id')->nullable()->index();
                $table->string('method', 10);
                $table->string('path', 500);
                $table->string('ip', 45)->nullable();
                $table->smallInteger('response_status')->nullable();
                $table->unsignedSmallInteger('duration_ms')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // Push notification device tokens
        if (!Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('platform', 10); // ios, android, web
                $table->text('token');
                $table->string('app_version', 20)->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'token']);
            });
        }

        // Notification preferences
        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type', 60); // comment, follow, new_post, like, mention
                $table->boolean('in_app')->default(true);
                $table->boolean('email')->default(false);
                $table->boolean('push')->default(false);
                $table->timestamps();
                $table->unique(['user_id', 'type']);
            });
        }

        // Account deletion grace period + export tracking
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deletion_requested_at')) {
                $table->timestamp('deletion_requested_at')->nullable()->after('last_seen_at');
            }
            if (!Schema::hasColumn('users', 'data_export_token')) {
                $table->string('data_export_token', 64)->nullable()->after('deletion_requested_at');
            }
            if (!Schema::hasColumn('users', 'data_export_requested_at')) {
                $table->timestamp('data_export_requested_at')->nullable()->after('data_export_token');
            }
            if (!Schema::hasColumn('users', 'data_export_ready_at')) {
                $table->timestamp('data_export_ready_at')->nullable()->after('data_export_requested_at');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('api_audit_logs');
        Schema::dropIfExists('user_sessions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['deletion_requested_at', 'data_export_token', 'data_export_requested_at', 'data_export_ready_at']);
        });
    }
};
