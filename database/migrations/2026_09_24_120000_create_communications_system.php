<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Communications campaigns table - unified message system
        if (!Schema::hasTable('communication_campaigns')) {
            Schema::create('communication_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();

                // Content
                $table->string('subject');
                $table->longText('html_content');
                $table->longText('text_content')->nullable();

                // Channel configuration (can send via multiple channels)
                $table->json('channels')->default('["email"]'); // email, in_app, announcement, push

                // Targeting
                $table->enum('target_type', [
                    'all_users', 'activated_users', 'inactive_users',
                    'segment', 'tag', 'custom_list',
                    'newsletter_subscribers'
                ])->default('newsletter_subscribers');
                $table->json('target_config')->nullable(); // Stores segment/tag/list config

                // Scheduling
                $table->enum('schedule_type', ['immediate', 'scheduled', 'recurring'])
                    ->default('immediate');
                $table->timestamp('scheduled_at')->nullable();
                $table->string('recurrence_rule')->nullable(); // Cron-like: daily, weekly, monthly
                $table->timestamp('recurrence_end_at')->nullable();

                // Announcement specific
                $table->timestamp('announcement_start_at')->nullable();
                $table->timestamp('announcement_end_at')->nullable();
                $table->string('announcement_position')->default('top'); // top, bottom, modal, sidebar
                $table->string('announcement_type')->default('info'); // info, warning, success, promotion

                // Status tracking
                $table->enum('status', [
                    'draft', 'scheduled', 'sending', 'sent',
                    'paused', 'cancelled', 'failed'
                ])->default('draft');

                // Metrics
                $table->unsignedBigInteger('total_recipients')->default(0);
                $table->unsignedBigInteger('sent_count')->default(0);
                $table->unsignedBigInteger('delivered_count')->default(0);
                $table->unsignedBigInteger('opened_count')->default(0);
                $table->unsignedBigInteger('clicked_count')->default(0);
                $table->unsignedBigInteger('bounced_count')->default(0);
                $table->unsignedBigInteger('unsubscribed_count')->default(0);

                // Rates
                $table->decimal('delivery_rate', 5, 2)->default(0); // percentage
                $table->decimal('open_rate', 5, 2)->default(0);
                $table->decimal('click_rate', 5, 2)->default(0);
                $table->decimal('bounce_rate', 5, 2)->default(0);

                // User tracking
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by')->nullable();

                // Timestamps
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                // Indexes
                $table->index(['status', 'created_at']);
                $table->index(['target_type', 'created_at']);
                $table->index(['scheduled_at', 'status']);
                $table->index(['channels']);
            });
        }

        // Communication campaign recipients - track individual sends
        if (!Schema::hasTable('communication_recipients')) {
            Schema::create('communication_recipients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->unsignedBigInteger('user_id');

                // Send status
                $table->enum('status', [
                    'pending', 'queued', 'sent', 'delivered',
                    'failed', 'bounced', 'unsubscribed'
                ])->default('pending');

                // Engagement tracking
                $table->boolean('opened')->default(false);
                $table->timestamp('opened_at')->nullable();
                $table->boolean('clicked')->default(false);
                $table->timestamp('clicked_at')->nullable();
                $table->string('clicked_url')->nullable();

                // Error tracking
                $table->text('error_message')->nullable();
                $table->integer('retry_count')->default(0);

                // Timestamps
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                // Indexes
                $table->foreign('campaign_id')->references('id')
                    ->on('communication_campaigns')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')
                    ->on('users')->cascadeOnDelete();
                $table->index(['campaign_id', 'status']);
                $table->index(['user_id', 'opened']);
                $table->index(['campaign_id', 'clicked']);
            });
        }

        // Announcements (legacy support + new unified system)
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_id')->nullable();
                $table->string('name');
                $table->text('title')->nullable();
                $table->string('type')->default('info');
                $table->longText('code'); // HTML content
                $table->timestamp('start_date')->useCurrent();
                $table->timestamp('end_date')->nullable();
                $table->timestamps();

                $table->index(['start_date', 'end_date']);
                $table->foreign('campaign_id')->references('id')
                    ->on('communication_campaigns')->nullOnDelete();
            });
        }

        // User announcement tracking (which announcements user has hidden)
        if (!Schema::hasTable('announcements_users')) {
            Schema::create('announcements_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('hidden_at')->useCurrent();

                $table->foreign('announcement_id')->references('id')
                    ->on('announcements')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')
                    ->on('users')->cascadeOnDelete();
                $table->unique(['announcement_id', 'user_id']);
            });
        }

        // Communication segments (for targeted campaigns)
        if (!Schema::hasTable('communication_segments')) {
            Schema::create('communication_segments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('created_by');
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('conditions'); // Filter conditions: {type: 'activity', value: '30_days_inactive'}
                $table->unsignedBigInteger('member_count')->default(0);
                $table->timestamp('last_calculated_at')->nullable();
                $table->timestamps();

                $table->index(['created_by', 'created_at']);
            });
        }

        // Communication segment members
        if (!Schema::hasTable('communication_segment_members')) {
            Schema::create('communication_segment_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('segment_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('added_at')->useCurrent();

                $table->foreign('segment_id')->references('id')
                    ->on('communication_segments')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')
                    ->on('users')->cascadeOnDelete();
                $table->unique(['segment_id', 'user_id']);
                $table->index(['segment_id']);
            });
        }

        // Communication templates
        if (!Schema::hasTable('communication_templates')) {
            Schema::create('communication_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('created_by');
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('category'); // newsletter, announcement, promotion, event
                $table->longText('html_template');
                $table->json('variables')->nullable(); // {{first_name}}, {{unsubscribe_link}}, etc.
                $table->string('thumbnail_url')->nullable();
                $table->integer('usage_count')->default(0);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->index(['category', 'created_at']);
            });
        }

        // Unsubscribe tracking
        if (!Schema::hasTable('communication_unsubscribes')) {
            Schema::create('communication_unsubscribes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->enum('unsubscribe_type', ['email', 'in_app', 'all'])->default('all');
                $table->text('reason')->nullable();
                $table->string('unsubscribe_token', 100)->unique();
                $table->timestamp('unsubscribed_at')->useCurrent();
                $table->timestamps();

                $table->foreign('user_id')->references('id')
                    ->on('users')->cascadeOnDelete();
                $table->index(['user_id', 'unsubscribe_type']);
            });
        }

        // Communication preferences per user
        if (!Schema::hasTable('user_communication_preferences')) {
            Schema::create('user_communication_preferences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();

                // Channel preferences
                $table->boolean('email_newsletters')->default(true);
                $table->boolean('email_announcements')->default(true);
                $table->boolean('in_app_notifications')->default(true);
                $table->boolean('push_notifications')->default(false);

                // Frequency preferences
                $table->enum('email_frequency', ['daily', 'weekly', 'monthly', 'never'])
                    ->default('weekly');
                $table->time('preferred_send_time')->default('09:00'); // 9 AM
                $table->string('preferred_timezone')->default('UTC');

                // Category preferences (JSON for flexibility)
                $table->json('category_preferences')->nullable(); // {promotions: true, updates: false}

                $table->timestamps();

                $table->foreign('user_id')->references('id')
                    ->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_communication_preferences');
        Schema::dropIfExists('communication_unsubscribes');
        Schema::dropIfExists('communication_templates');
        Schema::dropIfExists('communication_segment_members');
        Schema::dropIfExists('communication_segments');
        Schema::dropIfExists('announcements_users');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('communication_recipients');
        Schema::dropIfExists('communication_campaigns');
    }
};
