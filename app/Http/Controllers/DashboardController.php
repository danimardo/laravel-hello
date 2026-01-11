<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the dashboard/counter page.
     */
    public function index(Request $request): View|JsonResponse
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        // Get counter value from session
        $counter = $request->session()->get('counter', 0);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                        'is_admin' => $user->isAdmin(),
                    ],
                    'counter' => $counter,
                ],
            ]);
        }

        return view('counter.index', [
            'user' => $user,
            'counter' => $counter,
        ]);
    }

    /**
     * Show user profile.
     */
    public function profile(Request $request): View
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        return view('auth.profile', [
            'user' => $user,
        ]);
    }

    /**
     * Get current user data.
     */
    public function currentUser(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'is_admin' => $user->isAdmin(),
                'created_at' => $user->created_at,
            ],
        ]);
    }
}
