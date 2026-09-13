<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForbidBannedUser
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->user() && $request->user()->isBanned()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Your account has been suspended.');
        }

        return $next($request);
    }
}
