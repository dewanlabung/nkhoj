<?php

namespace App\Http\Controllers;

use App\Models\BroadcastChannel;
use App\Models\ChannelMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BroadcastChannelController extends Controller
{
    public function index()
    {
        $channels = BroadcastChannel::withCount('subscribers')
            ->latest()
            ->paginate(20);

        $myChannels = auth()->check()
            ? BroadcastChannel::where('user_id', auth()->id())->latest()->get()
            : collect();

        return view('channels.index', compact('channels', 'myChannels'));
    }

    public function create()
    {
        return view('channels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
        ]);

        $slug = Str::slug($request->name);
        $base = $slug;
        $i    = 1;
        while (BroadcastChannel::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $channel = BroadcastChannel::create([
            'user_id'     => auth()->id(),
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
        ]);

        // Owner auto-subscribes
        $channel->subscribers()->attach(auth()->id(), ['subscribed_at' => now()]);
        $channel->increment('subscriber_count');

        return redirect('/channels/' . $channel->slug)->with('success', 'Channel created!');
    }

    public function show(BroadcastChannel $broadcastChannel)
    {
        $broadcastChannel->loadCount('subscribers');
        $messages = $broadcastChannel->messages()->with('reactions')->paginate(30);
        $subscribed = auth()->check() && $broadcastChannel->isSubscribedBy(auth()->user());

        return view('channels.show', [
            'channel'    => $broadcastChannel,
            'messages'   => $messages,
            'subscribed' => $subscribed,
        ]);
    }

    public function subscribe(BroadcastChannel $broadcastChannel)
    {
        $user = auth()->user();
        if ($broadcastChannel->isSubscribedBy($user)) {
            $broadcastChannel->subscribers()->detach($user->id);
            $broadcastChannel->decrement('subscriber_count');
            $subscribed = false;
        } else {
            $broadcastChannel->subscribers()->attach($user->id, ['subscribed_at' => now()]);
            $broadcastChannel->increment('subscriber_count');
            $subscribed = true;
        }

        if (request()->expectsJson()) {
            return response()->json(['subscribed' => $subscribed, 'count' => $broadcastChannel->fresh()->subscriber_count]);
        }
        return back();
    }

    public function broadcast(Request $request, BroadcastChannel $broadcastChannel)
    {
        abort_unless(auth()->id() === $broadcastChannel->user_id, 403);

        $request->validate([
            'body'  => 'required|string|max:5000',
        ]);

        $message = ChannelMessage::create([
            'channel_id' => $broadcastChannel->id,
            'body'       => $request->body,
        ]);

        return back()->with('success', 'Message sent to ' . number_format($broadcastChannel->subscriber_count) . ' subscribers.');
    }

    public function react(Request $request, ChannelMessage $channelMessage)
    {
        $request->validate(['emoji' => 'required|string|max:10']);

        $existing = $channelMessage->reactions()->where('user_id', auth()->id())->first();
        if ($existing) {
            $existing->delete();
            $channelMessage->decrement('reactions_count');
        } else {
            $channelMessage->reactions()->create(['user_id' => auth()->id(), 'emoji' => $request->emoji]);
            $channelMessage->increment('reactions_count');
        }

        return response()->json(['count' => $channelMessage->fresh()->reactions_count]);
    }
}
