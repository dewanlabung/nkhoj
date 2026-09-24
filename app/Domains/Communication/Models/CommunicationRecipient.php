<?php

namespace App\Domains\Communication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationRecipient extends Model
{
    protected $table = 'communication_recipients';

    protected $fillable = [
        'campaign_id', 'user_id', 'status',
        'opened', 'opened_at', 'clicked', 'clicked_at', 'clicked_url',
        'error_message', 'retry_count', 'sent_at',
    ];

    protected $casts = [
        'opened' => 'boolean',
        'clicked' => 'boolean',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CommunicationCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function markAsSent(): void
    {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();
    }

    public function markAsDelivered(): void
    {
        $this->status = 'delivered';
        $this->save();
    }

    public function markAsOpened(): void
    {
        $this->opened = true;
        $this->opened_at = now();
        $this->save();

        $this->campaign()->increment('opened_count');
    }

    public function markAsClicked($url = null): void
    {
        $this->clicked = true;
        $this->clicked_at = now();
        if ($url) {
            $this->clicked_url = $url;
        }
        $this->save();

        $this->campaign()->increment('clicked_count');
    }

    public function markAsUnsubscribed(): void
    {
        $this->status = 'unsubscribed';
        $this->save();

        $this->campaign()->increment('unsubscribed_count');
    }

    public function markAsBounced(): void
    {
        $this->status = 'bounced';
        $this->save();

        $this->campaign()->increment('bounced_count');
    }

    public function setError($message): void
    {
        $this->error_message = $message;
        $this->status = 'failed';
        $this->retry_count += 1;
        $this->save();
    }
}
