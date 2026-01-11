<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SessionTimeoutMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

test('middleware allows active session', function () {
    // Arrange: Create user and set recent activity
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    $request = Request::create('/counter', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    // Set last activity to 5 minutes ago
    Session::put('last_activity_time', now()->subMinutes(5)->timestamp);

    // Act
    $middleware = new SessionTimeoutMiddleware();
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert: Session is still valid
    expect($response->getStatusCode())->toBe(200);
});

test('middleware blocks expired session', function () {
    // Arrange: Create user and set old activity
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    $request = Request::create('/counter', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    // Set last activity to 3 hours ago (past 2 hour limit)
    Session::put('last_activity_time', now()->subHours(3)->timestamp);

    // Act
    $middleware = new SessionTimeoutMiddleware();
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert: Session expired, redirected to login
    expect($response->getStatusCode())->toBe(302); // Redirect
    expect($response->headers->get('Location'))->toContain('login');
});

test('middleware sets activity time for new session', function () {
    // Arrange: Create user without activity time
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    $request = Request::create('/counter', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    // No last_activity_time set

    // Act
    $middleware = new SessionTimeoutMiddleware();
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert: Activity time was set
    expect(Session::has('last_activity_time'))->toBeTrue();
    expect($response->getStatusCode())->toBe(200);
});

test('middleware allows unauthenticated user', function () {
    // Arrange: No user
    $request = Request::create('/login', 'GET');
    $request->setUserResolver(function () {
        return null;
    });

    // Act
    $middleware = new SessionTimeoutMiddleware();
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert: Pass through for unauthenticated users
    expect($response->getStatusCode())->toBe(200);
});

test('middleware updates activity time on each request', function () {
    // Arrange: Create user with old activity
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    $request = Request::create('/counter', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    // Set last activity to 30 minutes ago
    Session::put('last_activity_time', now()->subMinutes(30)->timestamp);
    $oldActivityTime = Session::get('last_activity_time');

    // Act
    $middleware = new SessionTimeoutMiddleware();
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert: Activity time was updated
    expect(Session::get('last_activity_time'))->toBeGreaterThan($oldActivityTime);
    expect($response->getStatusCode())->toBe(200);
});

test('middleware returns JSON response for expired session on API requests', function () {
    // Arrange: Create user and set old activity
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    $request = Request::create('/api/counter', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    $request->headers->set('Accept', 'application/json');

    // Set last activity to 3 hours ago
    Session::put('last_activity_time', now()->subHours(3)->timestamp);

    // Act
    $middleware = new SessionTimeoutMiddleware();
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert: Returns JSON response
    expect($response->getStatusCode())->toBe(401);
    $responseData = json_decode($response->getContent(), true);
    expect($responseData['success'])->toBeFalse();
    expect($responseData['message'])->toContain('sesión ha expirado');
});

test('middleware handles session at exact expiration boundary', function () {
    // Arrange: Create user and set activity at exact 2 hour boundary
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    $request = Request::create('/counter', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    // Set last activity to exactly 2 hours ago
    Session::put('last_activity_time', now()->subHours(2)->timestamp);

    // Act
    $middleware = new SessionTimeoutMiddleware();
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert: At exact boundary, session is still valid (within tolerance)
    expect($response->getStatusCode())->toBe(200);
});
