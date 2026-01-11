<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\LoginRateLimitMiddleware;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Tests\TestCase;
use Mockery;

uses(TestCase::class);

test('middleware allows active user to proceed', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'status' => 'active',
        'failed_attempts' => 0,
    ]);

    $request = Request::create('/login', 'POST', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->andReturn($user);
    $userRepository->shouldReceive('unlockExpiredUsers')
        ->once();

    // Act
    $middleware = new LoginRateLimitMiddleware($userRepository);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert
    expect($response->getStatusCode())->toBe(200);
});

test('middleware blocks inactive user', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'status' => 'inactive',
    ]);

    $request = Request::create('/login', 'POST', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->andReturn($user);

    // Act
    $middleware = new LoginRateLimitMiddleware($userRepository);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert
    expect($response->getStatusCode())->toBe(403);
    expect($response->getContent())->toContain('Cuenta desactivada');
});

test('middleware blocks temporarily blocked user', function () {
    // Arrange
    $user = User::factory()->tempBlocked()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'locked_until' => now()->addHour(),
    ]);

    $request = Request::create('/login', 'POST', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->andReturn($user);

    // Act
    $middleware = new LoginRateLimitMiddleware($userRepository);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert
    expect($response->getStatusCode())->toBe(429);
    expect($response->getContent())->toContain('Demasiados intentos');
});

test('middleware handles non-existent user gracefully', function () {
    // Arrange
    $request = Request::create('/login', 'POST', [
        'username' => 'nonexistent',
        'password' => 'password123',
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->andReturn(null);
    $userRepository->shouldReceive('unlockExpiredUsers')
        ->once();

    // Act
    $middleware = new LoginRateLimitMiddleware($userRepository);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert
    expect($response->getStatusCode())->toBe(200);
});

test('middleware handles user with expired lock', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'status' => 'temp_blocked',
        'locked_until' => now()->subMinutes(30), // Already expired
    ]);

    $request = Request::create('/login', 'POST', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->andReturn($user);
    $userRepository->shouldReceive('unlockExpiredUsers')
        ->once();

    // Act
    $middleware = new LoginRateLimitMiddleware($userRepository);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert
    expect($response->getStatusCode())->toBe(200);
});

test('middleware handles requests without username or email', function () {
    // Arrange
    $request = Request::create('/login', 'POST', [
        'password' => 'password123',
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('unlockExpiredUsers')
        ->once();

    // Act
    $middleware = new LoginRateLimitMiddleware($userRepository);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert
    expect($response->getStatusCode())->toBe(200);
});

test('middleware normalizes identifier to lowercase', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'TestUser',
        'email' => 'Test@Example.Com',
        'status' => 'active',
    ]);

    $request = Request::create('/login', 'POST', [
        'username' => 'TESTUSER', // Uppercase
        'password' => 'password123',
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return strtolower($arg) === 'testuser';
        }))
        ->andReturn($user);
    $userRepository->shouldReceive('unlockExpiredUsers')
        ->once();

    // Act
    $middleware = new LoginRateLimitMiddleware($userRepository);
    $response = $middleware->handle($request, function ($req) {
        return response('OK', 200);
    });

    // Assert
    expect($response->getStatusCode())->toBe(200);
});
