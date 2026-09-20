<?php

namespace App\Http\Middleware;

use App\Models\UserEngagement\UserSession;
use Closure;
use Illuminate\Http\Request;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $due = !$user->last_seen_at || now()->diffInMinutes($user->last_seen_at) > 5;

            // Also refresh when the current session has no geo data yet
            if (!$due) {
                try {
                    $sid = session()->getId();
                    $due = $sid && UserSession::where('session_id', $sid)->whereNull('city')->whereNull('country')->exists();
                } catch (\Throwable) {}
            }

            if ($due) {
                $user->timestamps = false;
                $user->update(['last_seen_at' => now()]);
                $user->timestamps = true;

                try {
                    UserSession::upsertForRequest($request, $user->id);
                } catch (\Throwable) {
                    // silently skip if user_sessions table not yet migrated
                }
            }
        }
        return $next($request);
    }
}
