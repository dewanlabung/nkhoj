<?php

namespace App\Domains\Account\Services;

use App\Models\User;
use App\Services\HibpService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountService
{
    public function __construct(private HibpService $hibp) {}

    public function updatePersonalInfo(User $user, array $data): void
    {
        $user->update(array_intersect_key($data, array_flip([
            'name', 'first_name', 'last_name', 'username', 'bio', 'website',
        ])));
    }

    public function updateAvatar(User $user, UploadedFile $file): string
    {
        $path = $file->store('avatars', 'public');
        $url  = Storage::url($path);
        $user->update(['avatar_url' => $url]);
        return $url;
    }

    /** @throws \Illuminate\Validation\ValidationException */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        if ($this->hibp->isBreached($newPassword)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'password' => 'This password has appeared in a known data breach. Please choose a different password.',
            ]);
        }

        $user->update(['password' => $newPassword]);
    }
}
