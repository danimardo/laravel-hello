<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;

// Rutas públicas
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Rutas de autenticación
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
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
