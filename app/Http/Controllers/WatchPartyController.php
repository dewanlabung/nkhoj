<?php

namespace App\Http\Controllers;

use App\Models\WatchParty;
use App\Models\WatchPartyMember;
use App\Models\WatchPartyMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WatchPartyController extends Controller
{
    public function index()
    {
        $parties = WatchParty::whereIn('status', ['waiting', 'playing', 'paused'])
            ->with('host')
            ->withCount('members')
            ->latest()
            ->paginate(12);
        return view('watch-party.index', compact('parties'));
    }

    public function create()
    {
        return view('watch-party.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:200',
            'video_url' => 'required|url|max:500',
        ]);

        $party = WatchParty::create([
            'uuid'      => Str::uuid(),
            'host_id'   => auth()->id(),
            'title'     => $data['title'],
            'video_url' => $data['video_url'],
            'status'    => 'waiting',
            'join_code' => strtoupper(Str::random(8)),
        ]);

        // Host joins automatically
        WatchPartyMember::create(['watch_party_id' => $party->id, 'user_id' => auth()->id(), 'joined_at' => now()]);

        return redirect("/watch-party/{$party->join_code}");
    }

    public function show(string $code)
    {
        $party = WatchParty::where('join_code', $code)->with('host')->firstOrFail();
        $messages = $party->messages()->with('user')->orderBy('id')->limit(100)->get();
        $members  = $party->members()->with('user')->get();
        $isMember = auth()->check() && $party->isMember(auth()->user());
        $isHost   = auth()->check() && auth()->id() === $party->host_id;
        return view('watch-party.show', compact('party', 'messages', 'members', 'isMember', 'isHost'));
    }

    public function join(Request $request, string $code)
    {
        $party = WatchParty::where('join_code', $code)->firstOrFail();
        if ($party->status === 'ended') {
            return response()->json(['error' => 'Party has ended'], 422);
        }
        WatchPartyMember::firstOrCreate(
            ['watch_party_id' => $party->id, 'user_id' => auth()->id()],
            ['joined_at' => now()]
        );
        return response()->json(['joined' => true]);
    }

    public function sync(Request $request, WatchParty $watchParty)
    {
        abort_unless(auth()->id() === $watchParty->host_id, 403);
        $data = $request->validate([
            'status'           => 'required|in:waiting,playing,paused,ended',
            'playback_seconds' => 'required|integer|min:0',
        ]);
        $watchParty->update([...$data, 'sync_at' => now()]);
        return response()->json(['synced' => true]);
    }

    public function getSync(WatchParty $watchParty)
    {
        return response()->json([
            'status'           => $watchParty->status,
            'playback_seconds' => $watchParty->playback_seconds,
            'sync_at'          => $watchParty->sync_at?->timestamp,
        ]);
    }

    public function sendMessage(Request $request, WatchParty $watchParty)
    {
        $data = $request->validate(['body' => 'required|string|max:300']);
        $msg  = WatchPartyMessage::create([
            'watch_party_id' => $watchParty->id,
            'user_id'        => auth()->id(),
            'body'           => $data['body'],
            'at_seconds'     => $watchParty->playback_seconds,
        ]);
        $msg->load('user');
        return response()->json(['id' => $msg->id, 'name' => $msg->user->name, 'body' => $msg->body, 'time' => $msg->created_at->diffForHumans()]);
    }

    public function pollMessages(Request $request, WatchParty $watchParty)
    {
        $since = $request->query('since', 0);
        $msgs  = WatchPartyMessage::where('watch_party_id', $watchParty->id)
            ->where('id', '>', $since)
            ->with('user')
            ->orderBy('id')
            ->limit(50)
            ->get();
        return response()->json([
            'messages' => $msgs->map(fn($m) => ['id' => $m->id, 'name' => $m->user->name, 'body' => $m->body, 'time' => $m->created_at->diffForHumans()]),
            'sync'     => ['status' => $watchParty->status, 'playback_seconds' => $watchParty->playback_seconds, 'sync_at' => $watchParty->sync_at?->timestamp],
        ]);
    }
}
