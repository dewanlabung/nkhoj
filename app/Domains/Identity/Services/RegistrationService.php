<?php

namespace App\Domains\Identity\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationService
{
    public function register(array $data, bool $requireEmailConfirmation = false): User
    {
        $user = User::create([
            'uuid'             => Str::uuid(),
            'name'             => $data['name'],
            'username'         => $data['username'],
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            'role'             => 'author',
            'email_verified_at' => $requireEmailConfirmation ? null : now(),
        ]);

        if ($requireEmailConfirmation) {
            event(new Registered($user));
        }

        return $user;
    }
}
