<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (!$tenant) {
            return $next($request);
        }

        // Allow logout to proceed regardless of status
        if ($request->is('logout')) {
            return $next($request);
        }

        $isDeactivated = ($tenant->status === 'Deactivated');
        $expiresAt = $tenant->expires_at ? Carbon::parse($tenant->expires_at) : null;
        $isExpired = $expiresAt && $expiresAt->isPast();
        $isUpdating = (bool) ($tenant->getAttribute('is_updating') ?? false);

        if ($isUpdating) {
            $allowedDuringUpdate = [
                'admin/version/apply',
                'admin/version/rollback',
                'logout',
            ];

            foreach ($allowedDuringUpdate as $path) {
                if ($request->is($path) || $request->is(trim($path, '/') . '/*')) {
                    return $next($request);
                }
            }

            return response()->view('errors.tenant-unavailable', [
                'message' => $tenant->getAttribute('updating_message') ?: 'This tenant is currently updating. Please try again in a few moments.',
            ]);
        }

        // If Tenant is in a Problematic State (Deactivated OR Expired)
        if ($isDeactivated || $isExpired) {
            
            // Rule 1: Always let unauthenticated users see public/auth pages 
            // This prevents "Site Unavailable" hacker scouting and allows admins to actually reach the login form.
            if (!auth()->check()) {
                return $next($request);
            }

            // At this point, the user is undeniably logged in.
            $user = auth()->user();

            // Rule 2: If the user is NOT an admin, completely block them from the dashboard with a soft error.
            if ($user->role !== 'admin') {
                return response()->view('errors.contact-admin');
            } 
            
            // Rule 3: If the user IS an admin, let them proceed strictly to the subscription page.
            else {
                $allowedPaths = ['admin/subscription'];
                $isAllowed = false;
                
                foreach ($allowedPaths as $path) {
                    if ($request->is($path) || $request->is(trim($path, '/') . '/*')) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (!$isAllowed) {
                    $reason = $isDeactivated ? 'Your school has been deactivated.' : 'Your subscription has expired.';
                    return redirect('/admin/subscription')->with('warning', $reason . ' Please manage your plan to unlock all features.');
                }
            }
        }

        return $next($request);
    }
}
