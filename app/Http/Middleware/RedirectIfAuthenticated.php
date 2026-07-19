<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replaces Laravel's default RedirectIfAuthenticated so that when an already
 * authenticated user hits a guest route, they are sent to the dashboard for
 * the matching guard — not always to the frontend HOME.
 *
 * - admin guard → /admin
 * - web (staff)  → /panel
 * - default     → /
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect($this->dashboardFor($guard));
            }
        }

        return $next($request);
    }

    protected function dashboardFor(?string $guard): string
    {
        return match ($guard) {
            'admin' => '/admin',
            'customer' => '/portal',
            'web', null => '/panel',
            default => '/',
        };
    }
}
