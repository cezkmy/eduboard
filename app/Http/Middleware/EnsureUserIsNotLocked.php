<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EnsureUserIsNotLocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->locked_until && now()->lessThan($user->locked_until)) {
                $lockedUntil = Carbon::parse($user->locked_until)->format('M d, Y h:i A');
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                $message = "Your account has been temporarily locked until {$lockedUntil}. Please contact the administrator.";
                
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => $message], 403);
                }
                
                $loginRoute = (function_exists('tenant') && tenant()) ? route('tenant.login') : route('login');
                return redirect($loginRoute)->with('error', $message);
            }
        }

        return $next($request);
    }
}
