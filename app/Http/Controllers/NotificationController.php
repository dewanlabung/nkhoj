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

    public function recent()
    {
        $rows = auth()->user()->nkhojNotifications()->latest()->limit(8)->get();
        auth()->user()->nkhojNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        $icons = [
            'comment'  => '💬',
            'follow'   => '👥',
            'new_post' => '📰',
            'reaction' => '❤️',
            'bookmark' => '🔖',
        ];

        $items = $rows->map(function ($n) use ($icons) {
            $data = $n->data ?? [];
            $text = match ($n->type) {
                'comment'  => ($data['commenter'] ?? 'Someone') . ' commented on "' . ($data['post_title'] ?? 'your post') . '"',
                'follow'   => ($data['follower'] ?? 'Someone') . ' followed you',
                'new_post' => ($data['author_name'] ?? 'Someone') . ' published "' . ($data['post_title'] ?? 'a new post') . '"',
                'reaction' => 'Someone reacted to your post',
                default    => 'New notification',
            };
            $url = match ($n->type) {
                'comment'  => '/posts/' . ($data['post_slug'] ?? ''),
                'follow'   => '/profile/' . ($data['username'] ?? ''),
                'new_post' => '/posts/' . ($data['post_slug'] ?? ''),
                default    => '/notifications',
            };
            return [
                'id'   => $n->id,
                'icon' => $icons[$n->type] ?? '🔔',
                'text' => $text,
                'url'  => $url,
                'read' => !is_null($n->read_at),
                'ago'  => $n->created_at->diffForHumans(),
            ];
        });

        return response()->json(['items' => $items]);
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
