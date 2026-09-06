<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function vote(Request $request, int $pollId)
    {
        $poll = Poll::with('options')->findOrFail($pollId);

        if ($poll->isExpired()) {
            return response()->json(['error' => 'Poll has expired.'], 422);
        }

        $data = $request->validate([
            'option_id' => 'required|integer|exists:poll_options,id',
        ]);

        $sessionKey = $request->session()->getId();
        $userId     = auth()->id();

        // Check already voted
        $existing = PollVote::where('poll_id', $pollId)
            ->where(function ($q) use ($userId, $sessionKey) {
                $q->where('user_id', $userId)->orWhere('session_key', $sessionKey);
            })->exists();

        if ($existing) {
            return response()->json(['error' => 'Already voted.'], 422);
        }

        PollVote::create([
            'poll_id'        => $pollId,
            'poll_option_id' => $data['option_id'],
            'user_id'        => $userId,
            'session_key'    => $sessionKey,
        ]);

        PollOption::where('id', $data['option_id'])->increment('votes_count');

        // Return updated counts
        $poll->load('options');
        $total = $poll->options->sum('votes_count');
        $results = $poll->options->map(fn($o) => [
            'id'      => $o->id,
            'text'    => $o->text,
            'votes'   => $o->votes_count,
            'percent' => $total > 0 ? round(($o->votes_count / $total) * 100) : 0,
        ]);

        return response()->json(['total' => $total, 'results' => $results, 'voted' => true]);
    }

    public function results(int $pollId)
    {
        $poll  = Poll::with('options')->findOrFail($pollId);
        $total = $poll->options->sum('votes_count');
        return response()->json([
            'total'   => $total,
            'results' => $poll->options->map(fn($o) => [
                'id'      => $o->id,
                'text'    => $o->text,
                'votes'   => $o->votes_count,
                'percent' => $total > 0 ? round(($o->votes_count / $total) * 100) : 0,
            ]),
        ]);
    }
}
