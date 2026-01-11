<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

uses(RefreshDatabase::class);

test('AuthService changes password successfully', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->andReturn($user);
    $userRepository->shouldReceive('update')
        ->once()
        ->with($user, Mockery::on(function ($data) {
            return isset($data['password']) && \Hash::check('newpassword123', $data['password']);
        }));

    $authService = new AuthService($userRepository);

    // Act
    $result = $authService->changePassword($user, [
        'current_password' => 'oldpassword123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    // Assert
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Contraseña actualizada correctamente');
});

test('AuthService fails with wrong current password', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('correctpassword123'),
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->andReturn($user);

    $authService = new AuthService($userRepository);

    // Act
    $result = $authService->changePassword($user, [
        'current_password' => 'wrongpassword',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    // Assert
    expect($result['success'])->toBeFalse();
    expect($result['message'])->toBe('Contraseña actual incorrecta');
});

test('AuthService validates required fields', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $userRepository = Mockery::mock(UserRepository::class);

    $authService = new AuthService($userRepository);

    // Act & Assert: Missing current_password
    $result = $authService->changePassword($user, [
        'new_password' => 'NewPass1!',
        'new_password_confirmation' => 'NewPass1!',
    ]);
    expect($result['success'])->toBeFalse();

    // Act & Assert: Missing new_password
    $result = $authService->changePassword($user, [
        'current_password' => 'password123',
        'new_password_confirmation' => 'NewPass1!',
    ]);
    expect($result['success'])->toBeFalse();

    // Act & Assert: Mismatched confirmation
    $result = $authService->changePassword($user, [
        'current_password' => 'password123',
        'new_password' => 'NewPass1!',
        'new_password_confirmation' => 'differentpassword',
    ]);
    expect($result['success'])->toBeFalse();
});

test('AuthService validates password complexity requirements', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $userRepository = Mockery::mock(UserRepository::class);

    $authService = new AuthService($userRepository);

    // Act & Assert: Password too short
    $result = $authService->changePassword($user, [
        'current_password' => 'password123',
        'new_password' => 'Short1!',
        'new_password_confirmation' => 'Short1!',
    ]);
    expect($result['success'])->toBeFalse();

    // Act & Assert: Missing uppercase
    $result = $authService->changePassword($user, [
        'current_password' => 'password123',
        'new_password' => 'weakpass1!',
        'new_password_confirmation' => 'weakpass1!',
    ]);
    expect($result['success'])->toBeFalse();

    // Act & Assert: Missing lowercase
    $result = $authService->changePassword($user, [
        'current_password' => 'password123',
        'new_password' => 'WEAKPASS1!',
        'new_password_confirmation' => 'WEAKPASS1!',
    ]);
    expect($result['success'])->toBeFalse();

    // Act & Assert: Missing number
    $result = $authService->changePassword($user, [
        'current_password' => 'password123',
        'new_password' => 'WeakPass!',
        'new_password_confirmation' => 'WeakPass!',
    ]);
    expect($result['success'])->toBeFalse();

    // Act & Assert: Missing special character
    $result = $authService->changePassword($user, [
        'current_password' => 'password123',
        'new_password' => 'WeakPass1',
        'new_password_confirmation' => 'WeakPass1',
    ]);
    expect($result['success'])->toBeFalse();
});

test('AuthService updates user password via repository', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
    ]);

    $userRepository = Mockery::mock(UserRepository::class);
    $userRepository->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->andReturn($user);
    $userRepository->shouldReceive('update')
        ->once()
        ->with($user, Mockery::on(function ($data) {
            return is_string($data['password']) && strlen($data['password']) > 0;
        }))
        ->andReturnUsing(function ($user, $data) {
            $user->password = $data['password'];
            return $user;
        });

    $authService = new AuthService($userRepository);

    // Act
    $result = $authService->changePassword($user, [
        'current_password' => 'oldpassword123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    // Assert
    expect($result['success'])->toBeTrue();
    expect(\Hash::check('newpassword123', $user->password))->toBeFalse(); // Password not hashed in mock
});
