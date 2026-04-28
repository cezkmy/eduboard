<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionGated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $version): Response
    {
        if (!tenant_has_version($version)) {
            return redirect()->route('tenant.admin.system.update')
                ->with('error', "This feature requires a system update to {$version}.");
        }

        return $next($request);
    }
}
