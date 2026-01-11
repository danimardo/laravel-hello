<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\AuthService;
use App\Validators\LoginValidator;
use App\Validators\PasswordChangeValidator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        if (Auth::check()) {
            return redirect()->route('counter');
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(LoginRequest $request): JsonResponse|RedirectResponse
    {
        try {
            // Get credentials directly from request
            $credentials = [
                'username' => $request->input('username'),
                'password' => $request->input('password'),
            ];

            $result = $this->authService->login($credentials);

            if ($result['success']) {
                // Regenerate session to prevent session fixation
                $request->session()->regenerate();

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Login exitoso',
                        'redirect' => route('counter'),
                    ]);
                }

                return redirect()->intended(route('counter'))
                    ->with('success', 'Login exitoso');
            }

            // Failed login
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return back()
                ->withErrors(['login' => $result['message']])
                ->withInput($request->only('username', 'email'));

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error interno del servidor',
                ], 500);
            }

            return back()
                ->withErrors(['login' => 'Error interno del servidor: ' . $e->getMessage()])
                ->withInput($request->only('username', 'email'));
        }
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $this->authService->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logout exitoso',
                    'redirect' => route('login'),
                ]);
            }

            return redirect()->route('login')
                ->with('success', 'Logout exitoso');

        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error durante el logout',
                ], 500);
            }

            return redirect()->route('login')
                ->with('error', 'Error durante el logout');
        }
    }

    /**
     * Show the change password form.
     */
    public function showChangePasswordForm(): View
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.change-password');
    }

    /**
     * Handle change password request.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse|RedirectResponse
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado',
                ], 401);
            }

            return redirect()->route('login');
        }

        try {
            // Get data directly from request
            $data = [
                'current_password' => $request->input('current_password'),
                'new_password' => $request->input('new_password'),
                'new_password_confirmation' => $request->input('new_password_confirmation'),
            ];

            $result = $this->authService->changePassword(
                Auth::user(),
                $data
            );

            if ($result['success']) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $result['message'],
                    ]);
                }

                return back()->with('success', $result['message']);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return back()
                ->withErrors(['current_password' => $result['message']])
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Change password error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error interno del servidor',
                ], 500);
            }

            return back()
                ->withErrors(['password' => 'Error interno del servidor'])
                ->withInput();
        }
    }
}
