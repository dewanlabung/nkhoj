<?php

namespace App\Core\Traits;

trait HasRoles
{
    public function roleLabel(): string
    {
        return match($this->role ?? 'member') {
            'admin'    => 'Super Admin',
            'editor'   => 'Editor',
            'reporter' => 'Author',
            default    => 'Member',
        };
    }

    public function roleBadgeClass(): string
    {
        return match($this->role ?? 'member') {
            'admin'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'editor'   => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            'reporter' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            default    => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
        };
    }

    public function isEditor(): bool
    {
        return in_array($this->role, ['editor', 'admin']);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMod(): bool
    {
        return in_array($this->role, ['admin', 'editor']);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) return true;
        $extra = $this->extra_permissions ?? [];
        return in_array($permission, $extra, true);
    }
}
