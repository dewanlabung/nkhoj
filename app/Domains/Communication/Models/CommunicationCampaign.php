<?php

namespace App\Domains\Communication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunicationCampaign extends Model
{
    use HasFactory;

    protected $table = 'communication_campaigns';

    protected $fillable = [
        'name', 'description', 'subject', 'html_content', 'text_content',
        'channels', 'target_type', 'target_config',
        'schedule_type', 'scheduled_at', 'recurrence_rule', 'recurrence_end_at',
        'announcement_start_at', 'announcement_end_at', 'announcement_position',
        'announcement_type', 'status',
        'total_recipients', 'sent_count', 'delivered_count',
        'opened_count', 'clicked_count', 'bounced_count', 'unsubscribed_count',
        'created_by', 'updated_by', 'sent_at', 'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'channels' => 'json',
        'target_config' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'recurrence_end_at' => 'datetime',
        'announcement_start_at' => 'datetime',
        'announcement_end_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(CommunicationRecipient::class, 'campaign_id');
    }

    public function getProgressPercentage(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }
        return round(($this->sent_count / $this->total_recipients) * 100, 1);
    }

    public function getDeliveryRate(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }
        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    public function getOpenRate(): float
    {
        if ($this->delivered_count === 0) {
            return 0;
        }
        return round(($this->opened_count / $this->delivered_count) * 100, 2);
    }

    public function getClickRate(): float
    {
        if ($this->delivered_count === 0) {
            return 0;
        }
        return round(($this->clicked_count / $this->delivered_count) * 100, 2);
    }

    public function markAsSending(): void
    {
        $this->status = 'sending';
        $this->sent_at = now();
        $this->save();
    }

    public function markAsCompleted(): void
    {
        $this->status = 'sent';
        $this->completed_at = now();
        $this->save();
    }

    public function markAsFailed(): void
    {
        $this->status = 'failed';
        $this->save();
    }

    public function pause(): void
    {
        $this->status = 'paused';
        $this->save();
    }

    public function resume(): void
    {
        if ($this->status === 'paused') {
            $this->status = 'sending';
            $this->save();
        }
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->save();
    }

    public function getTargetLabel(): string
    {
        return match($this->target_type) {
            'all_users' => 'All Users',
            'activated_users' => 'Activated Users',
            'inactive_users' => 'Inactive Users',
            'segment' => 'Custom Segment: ' . ($this->target_config['segment_name'] ?? 'N/A'),
            'tag' => 'Tag: ' . ($this->target_config['tag_name'] ?? 'N/A'),
            'custom_list' => 'Custom List',
            'newsletter_subscribers' => 'Newsletter Subscribers',
            default => 'Unknown',
        };
    }

    public function getChannelLabel(): string
    {
        $labels = [
            'email' => '📧 Email',
            'in_app' => '🔔 In-App',
            'announcement' => '📢 Announcement',
            'push' => '📱 Push',
        ];

        $channels = $this->channels ?: ['email'];
        return implode(', ', array_map(fn($ch) => $labels[$ch] ?? $ch, $channels));
    }
}
