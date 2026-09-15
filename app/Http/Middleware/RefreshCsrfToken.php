<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RefreshCsrfToken
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $request->session()->regenerateToken();
        }

        return $next($request);
    }
}
