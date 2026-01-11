<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;

// Rutas públicas
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// TEMPORARY DEBUG ROUTE - REMOVE AFTER TESTING
Route::post('/test-login', function (Illuminate\Http\Request $request) {
    try {
        echo "<h1>DEBUG LOGIN TEST</h1>";
        echo "<pre>";

        echo "1. Request received\n";
        echo "   Username: " . ($request->input('username') ?? 'N/A') . "\n";
        echo "   Password: " . ($request->input('password') ? '***' : 'N/A') . "\n";
        echo "   CSRF Token: " . ($request->has('_token') ? 'Present' : 'Missing') . "\n\n";

        $userRepo = new \App\Repositories\UserRepository();
        $authService = new \App\Services\AuthService($userRepo);

        echo "2. Calling AuthService->login()\n";
        $result = $authService->login([
            'username' => $request->input('username'),
            'password' => $request->input('password')
        ]);

        echo "3. Result:\n";
        print_r($result);

        if ($result['success']) {
            echo "\n4. SUCCESS! User authenticated: " . $result['user']->username . "\n";
            echo "5. Attempting to redirect to /counter\n";
            echo "6. REDIRECT would happen here to: " . route('counter') . "\n";
            echo "\n✅ LOGIN WORKS! The issue is in the controller or middleware.\n";
        } else {
            echo "\n4. FAILED!\n";
            echo "   Message: " . $result['message'] . "\n";
        }

        echo "</pre>";
    } catch (Exception $e) {
        echo "<h1>ERROR</h1>";
        echo "<pre>";
        echo "Exception: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString();
        echo "</pre>";
    }
})->name('test-login');

// Rutas de autenticación
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
    Route::get('/counter', [DashboardController::class, 'index'])->name('counter');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

    // Change password
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])
        ->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword'])
        ->name('change-password');
});

// Rutas de administración
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // User management routes
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserManagementController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
    Route::patch('/users/{id}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
    Route::patch('/users/{id}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // API routes for admin panel
    Route::get('/stats', [UserManagementController::class, 'stats'])->name('stats');
    Route::get('/search', [UserManagementController::class, 'search'])->name('search');
});
