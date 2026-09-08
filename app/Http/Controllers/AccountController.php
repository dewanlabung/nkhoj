<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        $user = auth()->user();
        $sub  = $user->activeSubscription();
        return view('account.home', compact('user', 'sub'));
    }

    public function personalInfo()
    {
        return view('account.personal-info', ['user' => auth()->user()]);
    }

    public function updatePersonalInfo(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name'       => 'required|string|max:100',
            'first_name' => 'nullable|string|max:60',
            'last_name'  => 'nullable|string|max:60',
            'username'   => 'nullable|string|max:40|alpha_dash|unique:users,username,' . $user->id,
            'bio'        => 'nullable|string|max:500',
            'website'    => 'nullable|url|max:200',
        ]);

        $user->update($request->only('name', 'first_name', 'last_name', 'username', 'bio', 'website'));

        return back()->with('success', 'Personal info updated.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);
        $user = auth()->user();

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_url' => Storage::url($path)]);

        return back()->with('success', 'Avatar updated.');
    }

    public function security()
    {
        $user = auth()->user();
        $loginHistories = $user->loginHistories()->limit(20)->get();
        $socialAccounts = $user->socialAccounts()->get();
        return view('account.security', compact('user', 'loginHistories', 'socialAccounts'));
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => $request->password]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function subscriptions()
    {
        $user = auth()->user();
        $subs = Subscription::with('plan')
            ->where('user_id', $user->id)
            ->latest()
            ->get();
        return view('account.subscriptions', compact('user', 'subs'));
    }

    public function privacy()
    {
        return view('account.privacy', ['user' => auth()->user()]);
    }

    public function deleteAccount(Request $request)
    {
        $request->validate(['confirm' => 'required|in:DELETE']);
        $user = auth()->user();
        auth()->logout();
        $user->delete();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
