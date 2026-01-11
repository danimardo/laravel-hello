<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LoginRateLimitMiddleware
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $identifier = $request->input('username') ?? $request->input('email');

        if (!$identifier) {
            return $next($request);
        }

        // Normalize identifier
        $identifier = strtolower(trim($identifier));

        // Check if user exists
        $user = $this->userRepository->findByUsernameOrEmail($identifier);

        if ($user) {
            // Check if user is temporarily blocked
            if ($user->isLocked()) {
                $remainingTime = $this->userRepository->getRemainingLockTime($user);

                Log::warning('Rate limit exceeded - user account locked', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'locked_until' => $user->locked_until,
                    'failed_attempts' => $user->failed_attempts,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now(),
                ]);

                return response()->json([
                    'message' => 'Demasiados intentos fallidos. Intente nuevamente en ' . $this->formatRemainingTime($remainingTime) . '.',
                    'remaining_time' => $remainingTime,
                ], 429);
            }

            // Check if user status is inactive
            if ($user->isInactive()) {
                Log::warning('Rate limit check - inactive account', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now(),
                ]);

                return response()->json([
                    'message' => 'Cuenta desactivada. Contacte al administrador.',
                ], 403);
            }
        }

        // Unlock expired users before processing
        $this->userRepository->unlockExpiredUsers();

        return $next($request);
    }

    /**
     * Format remaining time in human-readable format.
     */
    private function formatRemainingTime(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' segundos';
        }

        if ($seconds < 3600) {
            return round($seconds / 60) . ' minutos';
        }

        return round($seconds / 3600) . ' horas';
    }
}
