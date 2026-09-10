<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function token(Request $request)
    {
        $data = $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:80',
            'scopes'      => 'nullable|array',
            'scopes.*'    => 'in:read,write,notifications',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if ($user->is_banned) {
            return response()->json(['error' => 'Account suspended'], 403);
        }

        $token = $user->createToken($data['device_name'], $data['scopes'] ?? ['read']);

        return response()->json([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user'       => [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'avatar_url' => $user->avatar_url,
                'email'      => $user->email,
                'role'       => $user->role,
            ],
        ]);
    }

    public function revoke(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Token revoked']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'username'   => $user->username,
            'avatar_url' => $user->avatar_url,
            'email'      => $user->email,
            'role'       => $user->role,
            'bio'        => $user->bio,
            'website'    => $user->website,
            'created_at' => $user->created_at,
        ]);
    }
}
