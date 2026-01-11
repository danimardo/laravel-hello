<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Tests\TestCase;
use Mockery;
use Symfony\Component\HttpFoundation\Response;

uses(TestCase::class);

test('middleware allows admin user to access admin routes', function () {
    // Arrange
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $request = Request::create('/admin/users', 'GET');
    $request->setUserResolver(function () use ($admin) {
        return $admin;
    });

    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('isAuthenticated')
        ->once()
        ->andReturn(true);
    $authService->shouldReceive('getCurrentUser')
        ->once()
        ->andReturn($admin);

    // Act
    $middleware = new RoleMiddleware($authService);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    }, 'admin');

    // Assert
    expect($response->getStatusCode())->toBe(200);
});

test('middleware blocks regular user from admin routes', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'role' => 'user',
    ]);

    $request = Request::create('/admin/users', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('isAuthenticated')
        ->once()
        ->andReturn(true);
    $authService->shouldReceive('getCurrentUser')
        ->once()
        ->andReturn($user);

    // Act
    $middleware = new RoleMiddleware($authService);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    }, 'admin');

    // Assert
    expect($response->getStatusCode())->toBe(403);
    expect($response->getContent())->toContain('Acceso denegado');
});

test('middleware redirects unauthenticated users to login', function () {
    // Arrange
    $request = Request::create('/admin/users', 'GET');

    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('isAuthenticated')
        ->once()
        ->andReturn(false);

    // Act
    $middleware = new RoleMiddleware($authService);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    }, 'admin');

    // Assert
    expect($response->getStatusCode())->toBe(302); // Redirect
});

test('middleware blocks when user is null', function () {
    // Arrange
    $request = Request::create('/admin/users', 'GET');
    $request->setUserResolver(function () {
        return null;
    });

    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('isAuthenticated')
        ->once()
        ->andReturn(true);
    $authService->shouldReceive('getCurrentUser')
        ->once()
        ->andReturn(null);

    // Act
    $middleware = new RoleMiddleware($authService);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    }, 'admin');

    // Assert
    expect($response->getStatusCode())->toBe(302); // Redirect to login
});

test('middleware allows regular user to access user routes', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'role' => 'user',
    ]);

    $request = Request::create('/counter', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('isAuthenticated')
        ->once()
        ->andReturn(true);
    $authService->shouldReceive('getCurrentUser')
        ->once()
        ->andReturn($user);

    // Act
    $middleware = new RoleMiddleware($authService);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    }, 'user');

    // Assert
    expect($response->getStatusCode())->toBe(200);
});

test('middleware allows admin to access user routes', function () {
    // Arrange
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $request = Request::create('/counter', 'GET');
    $request->setUserResolver(function () use ($admin) {
        return $admin;
    });

    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('isAuthenticated')
        ->once()
        ->andReturn(true);
    $authService->shouldReceive('getCurrentUser')
        ->once()
        ->andReturn($admin);

    // Act
    $middleware = new RoleMiddleware($authService);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    }, 'user');

    // Assert
    expect($response->getStatusCode())->toBe(200);
});

test('middleware returns 403 for unauthorized role', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'role' => 'user',
    ]);

    $request = Request::create('/some-admin-route', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('isAuthenticated')
        ->once()
        ->andReturn(true);
    $authService->shouldReceive('getCurrentUser')
        ->once()
        ->andReturn($user);

    // Act
    $middleware = new RoleMiddleware($authService);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    }, 'admin');

    // Assert
    expect($response->getStatusCode())->toBe(403);
    expect($response->getContent())->toContain('Acceso denegado');
});

test('middleware handles inactive user correctly', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'inactiveuser',
        'email' => 'inactive@example.com',
        'role' => 'user',
        'status' => 'inactive',
    ]);

    $request = Request::create('/counter', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('isAuthenticated')
        ->once()
        ->andReturn(true);
    $authService->shouldReceive('getCurrentUser')
        ->once()
        ->andReturn($user);

    // Act
    $middleware = new RoleMiddleware($authService);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    }, 'user');

    // Note: The role middleware doesn't check status, only role
    // Status is handled by authentication middleware
    // Assert: Should pass through as role check passes
    expect($response->getStatusCode())->toBe(200);
});
