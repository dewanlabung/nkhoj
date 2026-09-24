<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $table = 'email_campaigns';

    protected $fillable = [
        'subject',
        'html_content',
        'text_content',
        'total_recipients',
        'sent_count',
        'status',
        'recipient_filter',
        'template_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function getProgressPercentage(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }
        return round(($this->sent_count / $this->total_recipients) * 100, 1);
    }

    public function incrementSentCount($count = 1): void
    {
        $this->sent_count += $count;
        $this->save();
    }

    public function markAsSending(): void
    {
        $this->status = 'sending';
        $this->sent_at = now();
        $this->save();
    }

    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function markAsFailed(): void
    {
        $this->status = 'failed';
        $this->save();
    }

    public function getFilterLabel(): string
    {
        return match($this->recipient_filter) {
            'all' => 'All Users',
            'activated' => 'Activated Users',
            'inactive' => 'Inactive Users',
            'week' => 'Inactive (1 week)',
            'month' => 'Inactive (1 month)',
            '3months' => 'Inactive (3 months)',
            '6months' => 'Inactive (6 months)',
            '9months' => 'Inactive (9 months)',
            'year' => 'Inactive (1 year)',
            'newsletter' => 'Newsletter Subscribers',
            default => 'Unknown',
        };
    }
}
