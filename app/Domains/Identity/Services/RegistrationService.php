<?php

namespace App\Domains\Identity\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationService
{
    public function register(array $data): User
    {
        return User::create([
            'uuid'     => Str::uuid(),
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'author',
        ]);
    }
}
