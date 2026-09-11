<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InboxController extends Controller
{
    public function index()
    {
        $myId = auth()->id();
        $conversations = Conversation::whereHas('participants', fn($q) => $q->where('user_id', $myId))
            ->with(['participants.user', 'messages' => fn($q) => $q->visible()->limit(1)])
            ->get()
            ->sortByDesc(fn($c) => $c->messages->first()?->created_at)
            ->values();
        return view('inbox.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $myId = auth()->id();
        abort_unless($conversation->participants()->where('user_id', $myId)->exists(), 403);

        $messages = $conversation->messages()->visible()->with('sender')->orderBy('id')->get();

        // Mark as read
        $conversation->participants()->where('user_id', $myId)->update(['last_read_at' => now()]);

        // Mark view-once messages as viewed
        $messages->each(function ($msg) use ($myId) {
            if ($msg->is_view_once && !$msg->viewed_at && $msg->sender_id !== $myId) {
                $msg->update(['viewed_at' => now()]);
            }
        });

        $other = $conversation->otherUser($myId);
        return view('inbox.show', compact('conversation', 'messages', 'other'));
    }

    public function start(Request $request)
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id']);
        $myId = auth()->id();
        abort_if($data['user_id'] == $myId, 422);

        $conversation = Conversation::between($myId, $data['user_id']);
        if (!$conversation) {
            $conversation = Conversation::create(['uuid' => Str::uuid()]);
            ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $myId]);
            ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $data['user_id']]);
        }

        return response()->json(['conversation_id' => $conversation->id, 'redirect' => "/inbox/{$conversation->id}"]);
    }

    public function send(Request $request, Conversation $conversation)
    {
        $myId = auth()->id();
        abort_unless($conversation->participants()->where('user_id', $myId)->exists(), 403);

        $data = $request->validate([
            'body'        => 'nullable|string|max:2000',
            'is_view_once'=> 'boolean',
            'expires_in'  => 'nullable|in:300,3600,86400,604800', // 5min,1h,24h,7d in seconds
        ]);

        $expiresAt = isset($data['expires_in']) ? now()->addSeconds((int) $data['expires_in']) : null;

        $msg = DirectMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $myId,
            'body'            => $data['body'] ?? null,
            'is_view_once'    => $data['is_view_once'] ?? false,
            'expires_at'      => $expiresAt,
        ]);
        $msg->load('sender');

        return response()->json([
            'id'          => $msg->id,
            'body'        => $msg->body,
            'sender'      => $msg->sender->name,
            'is_view_once'=> $msg->is_view_once,
            'expires_at'  => $msg->expires_at?->toIso8601String(),
            'time'        => $msg->created_at->diffForHumans(),
        ]);
    }

    public function poll(Request $request, Conversation $conversation)
    {
        $myId = auth()->id();
        abort_unless($conversation->participants()->where('user_id', $myId)->exists(), 403);

        $since = $request->query('since', 0);
        $msgs  = DirectMessage::where('conversation_id', $conversation->id)
            ->where('id', '>', $since)
            ->visible()
            ->with('sender')
            ->orderBy('id')
            ->limit(30)
            ->get();

        return response()->json($msgs->map(fn($m) => [
            'id'           => $m->id,
            'body'         => $m->isViewableBy($myId) ? $m->body : null,
            'viewable'     => $m->isViewableBy($myId),
            'is_view_once' => $m->is_view_once,
            'sender'       => $m->sender->name,
            'mine'         => $m->sender_id === $myId,
            'expires_at'   => $m->expires_at?->toIso8601String(),
            'time'         => $m->created_at->diffForHumans(),
        ]));
    }
}
