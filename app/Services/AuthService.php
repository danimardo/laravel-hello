<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Attempt to authenticate a user with username or email.
     *
     * @param array $credentials
     * @return array{success: bool, user?: User, message?: string}
     */
    public function login(array $credentials): array
    {
        $validator = Validator::make($credentials, [
            'username' => 'required_without:email|string',
            'email' => 'required_without:username|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Credenciales inválidas',
            ];
        }

        // Get the identifier (username or email)
        $identifier = $credentials['username'] ?? $credentials['email'];
        $password = $credentials['password'];

        // Find user by username or email (case-insensitive)
        $user = $this->userRepository->findByUsernameOrEmail($identifier);

        // If user not found or password doesn't match, return generic error
        if (!$user || !Hash::check($password, $user->password)) {
            // Log failed login attempt
            Log::warning('Failed login attempt', [
                'identifier' => $identifier,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Credenciales inválidas',
            ];
        }

        // Check if user status is inactive
        if ($user->isInactive()) {
            Log::warning('Login attempt on inactive account', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Cuenta desactivada. Contacte al administrador.',
            ];
        }

        // Check if user is temporarily blocked
        if ($user->isLocked()) {
            Log::warning('Login attempt on locked account', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'locked_until' => $user->locked_until,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Cuenta bloqueada temporalmente. Intente más tarde.',
            ];
        }

        // Check if user is temporarily blocked (status check)
        if ($user->isTempBlocked()) {
            Log::warning('Login attempt on temp blocked account', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'failed_attempts' => $user->failed_attempts,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Cuenta bloqueada temporalmente por intentos fallidos. Intente más tarde.',
            ];
        }

        // Credentials are valid, reset failed attempts
        $this->userRepository->resetFailedAttempts($user);

        // Login the user
        Auth::login($user);

        // Set last activity time for session timeout
        Session::put('last_activity_time', now()->timestamp);

        Log::info('User logged in', [
            'user_id' => $user->id,
            'username' => $user->username,
            'timestamp' => now(),
        ]);

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        $user = Auth::user();

        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'username' => $user->username,
                'timestamp' => now(),
            ]);
        }

        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
    }

    /**
     * Get the currently authenticated user.
     */
    public function getCurrentUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Check if a user is authenticated.
     */
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Change password for authenticated user.
     *
     * @param User $user
     * @param array $data
     * @return array{success: bool, message: string}
     */
    public function changePassword(User $user, array $data): array
    {
        $validator = Validator::make($data, [
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>/?~])[a-zA-Z\d!@#$%^&*()\-_=+{};:,<.>/?~]{8,}$/'
            ],
            'new_password_confirmation' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Datos inválidos: La contraseña debe contener al menos una minúscula, una mayúscula, un número y un carácter especial',
            ];
        }

        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            Log::warning('Failed password change attempt - incorrect current password', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Contraseña actual incorrecta',
            ];
        }

        // Update password
        $this->userRepository->update($user, [
            'password' => bcrypt($data['new_password']),
        ]);

        // Log successful password change
        Log::info('Password changed successfully', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Contraseña actualizada correctamente',
        ];
    }

    /**
     * Validate login credentials without authenticating.
     */
    public function validateCredentials(array $credentials): bool
    {
        $user = $this->userRepository->findByUsernameOrEmail(
            $credentials['username'] ?? $credentials['email']
        );

        return $user && Hash::check($credentials['password'], $user->password);
    }

    /**
     * Get user by identifier (username or email).
     */
    public function getUserByIdentifier(string $identifier): ?User
    {
        return $this->userRepository->findByUsernameOrEmail($identifier);
    }

    /**
     * Check if user can login (not inactive or temp_blocked).
     */
    public function canLogin(User $user): bool
    {
        return !$user->isInactive() && !$user->isTempBlocked();
    }

    /**
     * Get the authentication attempt count for a user.
     */
    public function getFailedAttempts(User $user): int
    {
        return $user->failed_attempts;
    }

    /**
     * Check if user is locked due to too many failed attempts.
     */
    public function isLocked(User $user): bool
    {
        return $user->isLocked();
    }

    /**
     * Get remaining lock time in seconds.
     */
    public function getRemainingLockTime(User $user): int
    {
        if (!$user->isLocked() || !$user->locked_until) {
            return 0;
        }

        return max(0, $user->locked_until->diffInSeconds(now()));
    }
}
