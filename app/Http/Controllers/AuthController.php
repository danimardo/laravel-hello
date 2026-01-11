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
    protected LoginValidator $loginValidator;
    protected PasswordChangeValidator $passwordChangeValidator;

    public function __construct(
        AuthService $authService,
        LoginValidator $loginValidator,
        PasswordChangeValidator $passwordChangeValidator
    ) {
        $this->authService = $authService;
        $this->loginValidator = $loginValidator;
        $this->passwordChangeValidator = $passwordChangeValidator;
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
            // Use validator for additional validation if needed
            $credentials = $this->loginValidator->validate($request->validated());

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
            Log::error('Login error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error interno del servidor',
                ], 500);
            }

            return back()
                ->withErrors(['login' => 'Error interno del servidor'])
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
            // Use validator for additional validation if needed
            $validatedData = $this->passwordChangeValidator->validate($request->validated());

            $result = $this->authService->changePassword(
                Auth::user(),
                $validatedData
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
            Log::error('Change password error: ' . $e->getMessage());

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
