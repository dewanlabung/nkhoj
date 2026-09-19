<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Notifications\NotificationPreference;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $types = [
            'comment'  => 'Someone comments on your post',
            'follow'   => 'Someone follows you',
            'new_post' => 'Author you follow publishes',
            'like'     => 'Someone likes your post',
            'mention'  => 'Someone mentions you',
        ];
        try {
            NotificationPreference::defaultsFor($user->id);
            $prefs = $user->notificationPreferences()->get()->keyBy('type');
        } catch (\Throwable) {
            $prefs = collect();
        }
        return view('account.notifications', compact('prefs', 'types'));
    }

    public function update(Request $request)
    {
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Please log in first.');
        }

        $user  = auth()->user();
        $types = ['comment', 'follow', 'new_post', 'like', 'mention'];

        try {
            foreach ($types as $type) {
                NotificationPreference::updateOrCreate(
                    ['user_id' => $user->id, 'type' => $type],
                    [
                        'in_app' => $request->has("in_app_{$type}") && $request->input("in_app_{$type}") == '1',
                        'email'  => $request->has("email_{$type}") && $request->input("email_{$type}") == '1',
                        'push'   => $request->has("push_{$type}") && $request->input("push_{$type}") == '1',
                    ]
                );
            }
            return back()->with('success', 'Notification preferences saved.');
        } catch (\Throwable $e) {
            \Log::error('Notification preference save failed: ' . $e->getMessage());
            return back()->with('error', 'Could not save preferences. Please try again.');
        }
    }
}
