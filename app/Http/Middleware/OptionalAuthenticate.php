<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;

// Suppresses the authentication exception so guests can reach the route;
// policies handle actual authorization.
class OptionalAuthenticate extends Authenticate
{
    protected function unauthenticated($request, array $guards): void
    {
        // intentionally empty — let unauthenticated users through
    }
}
