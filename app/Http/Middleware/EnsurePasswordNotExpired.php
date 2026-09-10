<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordNotExpired
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->must_change_password) {
                // Allowed routes during mandatory password change
                $allowedRoutes = [
                    'password.change',
                    'password.change.update',
                    'logout',
                    'lang.switch',
                ];

                if (!$request->routeIs($allowedRoutes)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'You must change your temporary password before proceeding.',
                            'must_change_password' => true
                        ], 403);
                    }

                    return redirect()->route('password.change')->with('warning', 'You are logged in with a temporary password. Please change your password to continue.');
                }
            }
        }

        return $next($request);
    }
}
