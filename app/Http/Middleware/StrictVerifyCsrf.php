<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StrictVerifyCsrf
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip validation for GET, HEAD, and OPTIONS requests
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // Get the token from header or input
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        $sessionToken = $request->session()->token();

        // Debugging (optional, remove in production)
        // \Log::info('Strict CSRF Check:', [
        //     'request_token' => $token,
        //     'session_token' => $sessionToken,
        //     'match' => hash_equals($sessionToken, (string) $token)
        // ]);

        if (!$token || !hash_equals($sessionToken, (string) $token)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch.'], 419);
            }
            abort(419, 'CSRF token mismatch.');
        }

        return $next($request);
    }
}
