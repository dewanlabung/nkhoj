<?php

namespace App\Core\Traits;

use App\Models\Ban;

trait HasBans
{
    public function bans()
    {
        return $this->morphMany(Ban::class, 'bannable');
    }

    public function activeBan(): ?Ban
    {
        return $this->bans()->active()->latest()->first();
    }

    public function isBanned(): bool
    {
        return $this->bans()->active()->exists();
    }

    public function ban(string $comment = '', ?\DateTime $expiresAt = null, ?int $createdById = null): Ban
    {
        return $this->bans()->create([
            'comment' => $comment,
            'expired_at' => $expiresAt,
            'created_by_id' => $createdById,
        ]);
    }

    public function unban(): int
    {
        return $this->bans()->update(['expired_at' => now()]);
    }
}
