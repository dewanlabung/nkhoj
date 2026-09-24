<?php

namespace App\Domains\Communication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationSegmentMember extends Model
{
    public $timestamps = false;

    protected $table = 'communication_segment_members';

    protected $fillable = [
        'segment_id', 'user_id', 'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(CommunicationSegment::class, 'segment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
