<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        $start    = microtime(true);
        $response = $next($request);
        $duration = (int) ((microtime(true) - $start) * 1000);

        try {
            $user  = $request->user();
            $token = $user?->currentAccessToken();
            DB::table('api_audit_logs')->insert([
                'user_id'         => $user?->id,
                'token_id'        => $token?->id,
                'method'          => $request->method(),
                'path'            => substr($request->path(), 0, 500),
                'ip'              => $request->ip(),
                'response_status' => $response->getStatusCode(),
                'duration_ms'     => min($duration, 65535),
                'created_at'      => now(),
            ]);
        } catch (\Throwable) {}

        return $response;
    }
}
