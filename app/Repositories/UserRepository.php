<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    /**
     * Find a user by username (case-insensitive).
     */
    public function findByUsername(string $username): ?User
    {
        return User::whereRaw('LOWER(username) = ?', [strtolower($username)])->first();
    }

    /**
     * Find a user by email (case-insensitive).
     */
    public function findByEmail(string $email): ?User
    {
        return User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
    }

    /**
     * Find a user by username or email (case-insensitive).
     */
    public function findByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        return User::whereRaw('LOWER(username) = ?', [strtolower($usernameOrEmail)])
            ->orWhereRaw('LOWER(email) = ?', [strtolower($usernameOrEmail)])
            ->first();
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        // Normalize username and email (trim whitespace)
        $data['username'] = trim($data['username']);
        $data['email'] = trim($data['email']);

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::create($data);
    }

    /**
     * Update a user.
     */
    public function update(User $user, array $data): User
    {
        // Normalize username and email if provided
        if (isset($data['username'])) {
            $data['username'] = trim($data['username']);
        }

        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);
        }

        // Hash password if provided
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $user->fresh();
    }

    /**
     * Increment failed login attempts for a user.
     */
    public function incrementFailedAttempts(User $user): User
    {
        $attempts = $user->failed_attempts + 1;

        // Block user after 5 failed attempts
        if ($attempts >= 5) {
            $user->update([
                'failed_attempts' => $attempts,
                'status' => 'temp_blocked',
                'locked_until' => now()->addHour(),
            ]);

            // Log account lock
            Log::warning('User account locked due to too many failed attempts', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'failed_attempts' => $attempts,
                'locked_until' => now()->addHour(),
                'ip_address' => request()->ip(),
                'timestamp' => now(),
            ]);
        } else {
            $user->update([
                'failed_attempts' => $attempts,
            ]);

            // Log failed attempt
            Log::warning('Failed login attempt', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'failed_attempts' => $attempts,
                'max_attempts' => 5,
                'ip_address' => request()->ip(),
                'timestamp' => now(),
            ]);
        }

        return $user->fresh();
    }

    /**
     * Reset failed login attempts and unlock user.
     */
    public function resetFailedAttempts(User $user): User
    {
        $user->update([
            'failed_attempts' => 0,
            'status' => 'active',
            'locked_until' => null,
        ]);

        // Log successful unlock
        Log::info('Failed login attempts reset and user unlocked', [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'timestamp' => now(),
        ]);

        return $user->fresh();
    }

    /**
     * Unlock user after lock period expires.
     */
    public function unlockExpiredUsers(): void
    {
        User::where('status', 'temp_blocked')
            ->where('locked_until', '<=', now())
            ->update([
                'status' => 'active',
                'locked_until' => null,
                'failed_attempts' => 0,
            ]);
    }

    /**
     * Get all users by role.
     */
    public function getByRole(string $role): Collection
    {
        return User::where('role', $role)->get();
    }

    /**
     * Get all active users.
     */
    public function getActiveUsers(): Collection
    {
        return User::where('status', 'active')->get();
    }

    /**
     * Get all admin users.
     */
    public function getAdmins(): Collection
    {
        return User::where('role', 'admin')->get();
    }

    /**
     * Get all non-admin users.
     */
    public function getNonAdmins(): Collection
    {
        return User::where('role', 'user')->get();
    }

    /**
     * Check if username is unique (case-insensitive).
     */
    public function isUsernameUnique(string $username, ?int $excludeUserId = null): bool
    {
        $query = User::whereRaw('LOWER(username) = ?', [strtolower(trim($username))]);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return !$query->exists();
    }

    /**
     * Check if email is unique (case-insensitive).
     */
    public function isEmailUnique(string $email, ?int $excludeUserId = null): bool
    {
        $query = User::whereRaw('LOWER(email) = ?', [strtolower(trim($email))]);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return !$query->exists();
    }

    /**
     * Deactivate a user.
     */
    public function deactivate(User $user): User
    {
        $user->update(['status' => 'inactive']);

        return $user->fresh();
    }

    /**
     * Activate a user.
     */
    public function activate(User $user): User
    {
        $user->update([
            'status' => 'active',
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);

        return $user->fresh();
    }

    /**
     * Get remaining lock time in seconds.
     */
    public function getRemainingLockTime(User $user): int
    {
        if (!$user->locked_until) {
            return 0;
        }

        return max(0, $user->locked_until->diffInSeconds(now()));
    }

    /**
     * Get all users with pagination - Optimized with select.
     */
    public function getAllUsersPaginated(int $perPage = 15)
    {
        return User::select([
            'id',
            'username',
            'email',
            'role',
            'status',
            'failed_attempts',
            'locked_until',
            'created_at',
            'updated_at',
        ])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }

    /**
     * Get all users for admin (no pagination) - Optimized.
     */
    public function getAllUsersForAdmin()
    {
        return User::select([
            'id',
            'username',
            'email',
            'role',
            'status',
            'failed_attempts',
            'locked_until',
            'created_at',
            'updated_at',
        ])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Get user by ID.
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Get users count by role - Optimized with single query.
     */
    public function getUsersCountByRole(): array
    {
        $counts = User::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN role = "admin" THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN role = "user" THEN 1 ELSE 0 END) as users,
            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as inactive
        ')
        ->first()
        ->toArray();

        return [
            'total' => (int) $counts['total'],
            'admins' => (int) $counts['admins'],
            'users' => (int) $counts['users'],
            'active' => (int) $counts['active'],
            'inactive' => (int) $counts['inactive'],
        ];
    }

    /**
     * Search users - Optimized to prevent N+1.
     */
    public function searchUsers(string $query)
    {
        return User::select([
            'id',
            'username',
            'email',
            'role',
            'status',
            'failed_attempts',
            'locked_until',
            'created_at',
            'updated_at',
        ])
        ->where('username', 'like', "%{$query}%")
        ->orWhere('email', 'like', "%{$query}%")
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    }
}
