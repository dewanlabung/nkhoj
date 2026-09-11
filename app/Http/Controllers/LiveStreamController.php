<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\LiveStreamMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveStreamController extends Controller
{
    public function index()
    {
        $live  = LiveStream::where('status', 'live')->with('user')->latest()->paginate(12);
        $past  = LiveStream::where('status', 'ended')->with('user')->latest()->limit(8)->get();
        return view('live.index', compact('live', 'past'));
    }

    public function create()
    {
        return view('live.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'embed_url'   => 'nullable|url|max:500',
        ]);

        $stream = LiveStream::create([
            'uuid'       => Str::uuid(),
            'user_id'    => auth()->id(),
            'title'      => $data['title'],
            'description'=> $data['description'] ?? null,
            'embed_url'  => $data['embed_url'] ?? null,
            'status'     => 'live',
            'started_at' => now(),
        ]);

        return redirect("/live/{$stream->id}");
    }

    public function show(LiveStream $liveStream)
    {
        // Increment viewer count
        if ($liveStream->isLive()) {
            $liveStream->increment('viewer_count');
            if ($liveStream->viewer_count > $liveStream->peak_viewers) {
                $liveStream->update(['peak_viewers' => $liveStream->viewer_count]);
            }
        }
        $messages = $liveStream->messages()->with('user')->latest()->limit(50)->get()->reverse()->values();
        return view('live.show', compact('liveStream', 'messages'));
    }

    public function end(LiveStream $liveStream)
    {
        abort_unless(auth()->id() === $liveStream->user_id, 403);
        $liveStream->update(['status' => 'ended', 'ended_at' => now(), 'viewer_count' => 0]);
        return response()->json(['ended' => true]);
    }

    public function chat(Request $request, LiveStream $liveStream)
    {
        $data = $request->validate(['body' => 'required|string|max:500']);

        $msg = LiveStreamMessage::create([
            'live_stream_id' => $liveStream->id,
            'user_id'        => auth()->id(),
            'guest_name'     => auth()->check() ? null : 'Guest',
            'body'           => $data['body'],
        ]);
        $msg->load('user');

        return response()->json([
            'id'      => $msg->id,
            'name'    => $msg->displayName(),
            'body'    => $msg->body,
            'time'    => $msg->created_at->diffForHumans(),
        ]);
    }

    public function poll(Request $request, LiveStream $liveStream)
    {
        $since = $request->query('since', 0);
        $msgs  = LiveStreamMessage::where('live_stream_id', $liveStream->id)
            ->where('id', '>', $since)
            ->with('user')
            ->orderBy('id')
            ->limit(30)
            ->get();

        return response()->json([
            'messages'  => $msgs->map(fn($m) => ['id' => $m->id, 'name' => $m->displayName(), 'body' => $m->body, 'time' => $m->created_at->diffForHumans()]),
            'is_live'   => $liveStream->isLive(),
            'viewers'   => $liveStream->viewer_count,
        ]);
    }
}
