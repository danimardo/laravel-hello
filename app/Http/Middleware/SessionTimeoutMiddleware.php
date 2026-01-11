<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): RedirectResponse|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        // Only check for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $lastActivity = Session::get('last_activity_time');

        // If no last activity time is set, set it now
        if (!$lastActivity) {
            Session::put('last_activity_time', now()->timestamp);
            return $next($request);
        }

        // Check if session has expired (2 hours = 7200 seconds)
        $sessionLifetime = config('session.lifetime', 120) * 60;
        $timeSinceLastActivity = now()->timestamp - $lastActivity;

        if ($timeSinceLastActivity > $sessionLifetime) {
            $user = Auth::user();

            // Log session timeout
            Log::info('Session expired due to inactivity', [
                'user_id' => $user ? $user->id : null,
                'username' => $user ? $user->username : null,
                'session_lifetime' => $sessionLifetime,
                'time_since_last_activity' => $timeSinceLastActivity,
                'last_activity' => date('Y-m-d H:i:s', $lastActivity),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);

            // Session has expired, logout user
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            // Redirect to login with message
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Su sesión ha expirado después de 2 horas de inactividad. Por favor, inicie sesión nuevamente.');
        }

        // Update last activity time
        Session::put('last_activity_time', now()->timestamp);

        return $next($request);
    }
}
