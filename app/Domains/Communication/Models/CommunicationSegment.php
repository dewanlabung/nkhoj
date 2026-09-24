<?php

namespace App\Domains\Communication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationSegment extends Model
{
    protected $table = 'communication_segments';

    protected $fillable = [
        'name', 'description', 'conditions',
        'member_count', 'last_calculated_at', 'created_by',
    ];

    protected $casts = [
        'conditions' => 'json',
        'member_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_calculated_at' => 'datetime',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(CommunicationSegmentMember::class, 'segment_id');
    }

    public function recalculateMembers(): void
    {
        $members = $this->evaluateConditions();
        $this->member_count = $members->count();
        $this->last_calculated_at = now();
        $this->save();

        \DB::table('communication_segment_members')
            ->where('segment_id', $this->id)
            ->delete();

        $memberData = $members->map(function ($user) {
            return [
                'segment_id' => $this->id,
                'user_id' => $user->id,
                'added_at' => now(),
            ];
        })->chunk(500);

        foreach ($memberData as $chunk) {
            \DB::table('communication_segment_members')->insert($chunk->toArray());
        }
    }

    private function evaluateConditions()
    {
        $query = \App\Models\User::query();

        if (!$this->conditions) {
            return $query;
        }

        foreach ($this->conditions as $condition) {
            $type = $condition['type'] ?? null;
            $value = $condition['value'] ?? null;

            match($type) {
                'activity' => $query = $this->applyActivityFilter($query, $value),
                'status' => $query = $this->applyStatusFilter($query, $value),
                'custom' => $query = $this->applyCustomFilter($query, $value),
                default => null,
            };
        }

        return $query;
    }

    private function applyActivityFilter($query, $value)
    {
        return match($value) {
            '7_days_inactive' => $query->where('last_activity_at', '<', now()->subDays(7)),
            '30_days_inactive' => $query->where('last_activity_at', '<', now()->subDays(30)),
            '3_months_inactive' => $query->where('last_activity_at', '<', now()->subMonths(3)),
            '6_months_inactive' => $query->where('last_activity_at', '<', now()->subMonths(6)),
            default => $query,
        };
    }

    private function applyStatusFilter($query, $value)
    {
        return match($value) {
            'active' => $query->whereNotNull('email_verified_at'),
            'inactive' => $query->whereNull('email_verified_at'),
            'premium' => $query->where('is_premium', true),
            default => $query,
        };
    }

    private function applyCustomFilter($query, $value)
    {
        // Placeholder for custom filter logic
        return $query;
    }
}
