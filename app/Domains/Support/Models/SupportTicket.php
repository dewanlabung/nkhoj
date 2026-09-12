<?php

namespace App\Domains\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = ['requester_id', 'agent_id', 'subject', 'body', 'status', 'priority'];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportReply::class, 'ticket_id')->orderBy('created_at');
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'open'        => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-yellow-100 text-yellow-700',
            'pending'     => 'bg-orange-100 text-orange-700',
            'solved'      => 'bg-green-100 text-green-700',
            'closed'      => 'bg-gray-100 text-gray-600',
            default       => 'bg-gray-100 text-gray-600',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'pending'     => 'Pending',
            'solved'      => 'Solved',
            'closed'      => 'Closed',
            default       => ucfirst($this->status),
        };
    }
}
