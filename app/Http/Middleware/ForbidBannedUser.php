<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;

class ForbidBannedUser
{
    public function __construct(protected StatefulGuard $guard)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->user() && $request->user()->isBanned()) {
            $this->guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Your account has been suspended.');
        }

        return $next($request);
    }
}
