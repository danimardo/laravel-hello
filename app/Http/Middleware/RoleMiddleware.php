<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            Log::warning('Unauthorized access attempt - not authenticated', [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->url(),
                'method' => request()->method(),
                'timestamp' => now(),
            ]);

            return redirect()->route('login');
        }

        $user = $this->authService->getCurrentUser();

        if (!$user) {
            Log::warning('Unauthorized access attempt - no user found', [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->url(),
                'method' => request()->method(),
                'timestamp' => now(),
            ]);

            return redirect()->route('login');
        }

        // Check if user has the required role
        if ($role === 'admin' && !$user->isAdmin()) {
            Log::warning('Unauthorized access attempt - insufficient permissions (admin required)', [
                'user_id' => $user->id,
                'username' => $user->username,
                'user_role' => $user->role,
                'required_role' => 'admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->url(),
                'method' => request()->method(),
                'timestamp' => now(),
            ]);

            abort(403, 'Acceso denegado. Se requiere rol de administrador.');
        }

        if ($role === 'user' && !$user->isUser()) {
            Log::warning('Unauthorized access attempt - insufficient permissions (user required)', [
                'user_id' => $user->id,
                'username' => $user->username,
                'user_role' => $user->role,
                'required_role' => 'user',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->url(),
                'method' => request()->method(),
                'timestamp' => now(),
            ]);

            abort(403, 'Acceso denegado.');
        }

        return $next($request);
    }
}
