<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class UserManagementController
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = $request->input('q');

        if ($query) {
            $users = $this->userService->searchUsers($query);
        } else {
            $users = $this->userService->getAllUsers(15);
        }

        $stats = $this->userService->getUsersCountByRole();

        return view('admin.users.index', [
            'users' => $users,
            'stats' => $stats,
            'query' => $query,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $validatedData = $request->validate([
                'username' => 'required|string|min:3|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required|string',
                'role' => 'required|in:admin,user',
            ]);

            $user = $this->userService->createUser($validatedData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario creado exitosamente',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuario creado exitosamente');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear usuario: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Error al crear usuario'])
                ->withInput();
        }
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): View
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            abort(404, 'Usuario no encontrado');
        }

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(int $id): View
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            abort(404, 'Usuario no encontrado');
        }

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $validatedData = $request->validate([
                'username' => 'sometimes|required|string|min:3|max:255',
                'email' => 'sometimes|required|email|max:255',
                'password' => 'nullable|string|min:6|confirmed',
                'password_confirmation' => 'nullable|string|same:password',
                'role' => 'sometimes|required|in:admin,user',
                'status' => 'sometimes|required|in:active,inactive',
            ]);

            // Remove password_confirmation from data
            unset($validatedData['password_confirmation']);

            $user = $this->userService->updateUser($id, $validatedData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                    ],
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuario actualizado exitosamente');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar usuario: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Error al actualizar usuario'])
                ->withInput();
        }
    }

    /**
     * Deactivate the specified user.
     */
    public function deactivate(Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $user = $this->userService->deactivateUser($id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario desactivado exitosamente',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'status' => $user->status,
                    ],
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuario desactivado exitosamente');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->errors()['admin'][0] ?? 'Error al desactivar usuario',
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al desactivar usuario: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Error al desactivar usuario']);
        }
    }

    /**
     * Activate the specified user.
     */
    public function activate(Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $user = $this->userService->activateUser($id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario activado exitosamente',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'status' => $user->status,
                    ],
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuario activado exitosamente');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al activar usuario',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al activar usuario: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Error al activar usuario']);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $this->userService->deleteUser($id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario eliminado exitosamente',
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Usuario eliminado exitosamente');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->errors()['admin'][0] ?? 'Error al eliminar usuario',
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar usuario: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Error al eliminar usuario']);
        }
    }

    /**
     * Get user statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = $this->userService->getUsersCountByRole();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Search users (API endpoint).
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $users = $this->userService->searchUsers($query);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }
}
