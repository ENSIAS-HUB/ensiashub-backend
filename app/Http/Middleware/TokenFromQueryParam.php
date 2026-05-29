<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For endpoints that cannot set the Authorization header (e.g. EventSource / SSE),
 * allow the Sanctum token to be passed as a ?token= query parameter.
 *
 * Apply ONLY to specific routes (e.g. the notifications/stream endpoint).
 */
class TokenFromQueryParam
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->query('token');

        if ($token && !$request->hasHeader('Authorization')) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
