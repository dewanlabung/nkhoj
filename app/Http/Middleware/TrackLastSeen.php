<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (!$user->last_seen_at || now()->diffInMinutes($user->last_seen_at) > 5) {
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
