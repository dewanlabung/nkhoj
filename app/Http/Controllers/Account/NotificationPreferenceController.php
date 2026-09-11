<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
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
        $user  = auth()->user();
        $types = ['comment', 'follow', 'new_post', 'like', 'mention'];

        try {
            foreach ($types as $type) {
                NotificationPreference::updateOrCreate(
                    ['user_id' => $user->id, 'type' => $type],
                    [
                        'in_app' => $request->boolean("in_app_{$type}"),
                        'email'  => $request->boolean("email_{$type}"),
                        'push'   => $request->boolean("push_{$type}"),
                    ]
                );
            }
        } catch (\Throwable) {
            return back()->with('error', 'Could not save — run migrations first.');
        }

        return back()->with('success', 'Notification preferences saved.');
    }
}
