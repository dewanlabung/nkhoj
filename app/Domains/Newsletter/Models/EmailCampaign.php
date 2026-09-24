<?php

namespace App\Domains\Newsletter\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    protected $fillable = [
        'subject',
        'html_content',
        'text_content',
        'total_recipients',
        'sent_count',
        'status',
        'template_id',
    ];

    protected $casts = [
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
    ];

    public function incrementSentCount(): void
    {
        $this->increment('sent_count');

        if ($this->sent_count >= $this->total_recipients) {
            $this->update(['status' => 'completed']);
        }
    }

    public function markAsSending(): void
    {
        $this->update(['status' => 'sending']);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function getProgressPercentage(): float
    {
        return $this->total_recipients > 0 ? ($this->sent_count / $this->total_recipients) * 100 : 0;
    }
}
