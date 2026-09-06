<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->nkhojNotifications()->paginate(20);
        auth()->user()->nkhojNotifications()->whereNull('read_at')->update(['read_at' => now()]);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(int $id)
    {
        Notification::where('id', $id)->where('user_id', auth()->id())->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function unreadCount()
    {
        $count = auth()->user()->nkhojNotifications()->whereNull('read_at')->count();
        return response()->json(['count' => $count]);
    }
}
