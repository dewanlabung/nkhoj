<?php

namespace App\Core\Contracts;

interface BanService
{
    public function ban($model, string $comment = '', ?\DateTime $expiresAt = null, ?int $createdById = null);
    public function unban($model);
    public function isActive($model): bool;
    public function getActiveBan($model);
}
