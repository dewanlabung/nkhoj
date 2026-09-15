<?php

namespace App\Core\Services\Auth;

use App\Core\Contracts\BanService as BanServiceContract;
use App\Models\Ban;
use Illuminate\Database\Eloquent\Model;

class BanService implements BanServiceContract
{
    public function ban(
        $model,
        string $comment = '',
        ?\DateTime $expiresAt = null,
        ?int $createdById = null
    ) {
        return Ban::create([
            'bannable_type' => get_class($model),
            'bannable_id' => $model->id,
            'comment' => $comment,
            'expired_at' => $expiresAt,
            'created_by_id' => $createdById,
        ]);
    }

    public function unban($model): bool
    {
        return $model->bans()->update(['expired_at' => now()]);
    }

    public function isActive($model): bool
    {
        return $model->bans()->active()->exists();
    }

    public function getActiveBan($model): ?Ban
    {
        return $model->bans()->active()->latest()->first();
    }
}
