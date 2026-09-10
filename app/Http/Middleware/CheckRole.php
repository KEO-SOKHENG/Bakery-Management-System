<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        // Account status check
        if ($user->status !== 'active') {
            $msg = $user->status === 'suspended'
                ? 'Your account has been suspended. Please contact an administrator.'
                : 'Your account has been deactivated.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', $msg);
        }

        // Flatten roles if passed as a single comma-separated string or array
        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $subRole) {
                $allowedRoles[] = trim(strtolower($subRole));
            }
        }

        // Check if user's role is in the allowed roles list
        if (!empty($allowedRoles) && !in_array(strtolower($user->role), $allowedRoles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            // Redirect user to their default dashboard with error message
            $targetRoute = match (strtolower($user->role)) {
                'admin' => 'admin.dashboard',
                'manager' => 'manager.dashboard',
                'cashier' => 'cashier.dashboard',
                'baker' => 'admin.production',
                default => 'login',
            };

            // If user is already on their dashboard or attempting forbidden URL, render 403 or redirect
            if ($request->routeIs($targetRoute)) {
                abort(403, 'Unauthorized access.');
            }

            return redirect()->route($targetRoute)->with('error', 'Access Denied: You do not have permission to view that page.');
        }

        return $next($request);
    }
}
