<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function settings()
    {
        return view('account.settings');
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'                   => 'required|string|max:100',
            'username'               => ['required','string','max:50', Rule::unique('users')->ignore($user->id)],
            'email'                  => ['required','email','max:150', Rule::unique('users')->ignore($user->id)],
            'bio'                    => 'nullable|string|max:500',
            'website'                => 'nullable|url|max:200',
            'social_links.twitter'   => 'nullable|string|max:100',
            'social_links.instagram' => 'nullable|string|max:100',
            'social_links.facebook'  => 'nullable|string|max:100',
            'social_links.youtube'   => 'nullable|string|max:100',
            'social_links.tiktok'    => 'nullable|string|max:100',
        ]);

        $social = array_filter($request->input('social_links', []), fn($v) => !empty(trim((string)$v)));
        $user->update([
            'name'         => $data['name'],
            'username'     => $data['username'],
            'email'        => $data['email'],
            'bio'          => $data['bio'] ?? null,
            'website'      => $data['website'] ?? null,
            'social_links' => $social ?: null,
        ]);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function password()
    {
        return view('account.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully.');
    }
}
