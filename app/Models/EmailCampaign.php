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
        'template_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        $this->save();
    }

    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->save();
    }

    public function markAsFailed(): void
    {
        $this->status = 'failed';
        $this->save();
    }
}
