<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check User Status Middleware
 * 
 * Verifies that authenticated users have active status on every request.
 * This prevents inactive/suspended users from continuing sessions.
 * 
 * Applied to all admin routes to ensure real-time status enforcement.
 */
class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is authenticated, check their status
        if ($user) {
            // User is inactive - log them out immediately
            if ($user->isInactive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Your account has been deactivated. Please contact your administrator.');
            }

            // User is suspended - log them out immediately
            if ($user->isSuspended()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Your account has been suspended. Please contact your administrator.');
            }
        }

        return $next($request);
    }
}
