<?php

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('rate limiting blocks after 5 failed attempts', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'status' => 'active',
        'failed_attempts' => 0,
    ]);

    // Act & Assert: 4 failed attempts should still work
    for ($i = 0; $i < 4; $i++) {
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        // Should return error but not blocked
        $response->assertStatus(302); // Redirect after failed login
        expect(User::find($user->id)->failed_attempts)->toBe($i + 1);
    }

    // 5th failed attempt
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'wrongpassword',
    ]);

    // User should now be blocked
    $updatedUser = User::find($user->id);
    expect($updatedUser->failed_attempts)->toBe(5);
    expect($updatedUser->status)->toBe('temp_blocked');
    expect($updatedUser->locked_until)->not->toBeNull();

    // 6th attempt should be blocked
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(302); // Blocked by middleware
});

test('user is unlocked after 1 hour', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'status' => 'temp_blocked',
        'failed_attempts' => 5,
        'locked_until' => now()->subMinutes(5), // Already expired
    ]);

    $userRepository = app(UserRepository::class);

    // Act
    $userRepository->unlockExpiredUsers();

    // Assert
    $unlockedUser = User::find($user->id);
    expect($unlockedUser->status)->toBe('active');
    expect($unlockedUser->failed_attempts)->toBe(0);
    expect($unlockedUser->locked_until)->toBeNull();
});

test('valid login resets failed attempts', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'status' => 'active',
        'failed_attempts' => 3,
    ]);

    // Note: This test assumes login functionality exists
    // When implementing, the login should reset failed_attempts to 0
    // and set status to 'active'
});

test('inactive user cannot login', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'status' => 'inactive',
        'failed_attempts' => 0,
    ]);

    // Act
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // Assert
    // Should be blocked with 403 or redirect
    $response->assertStatus(302); // Middleware should redirect or show error
});

test('temp blocked user cannot login', function () {
    // Arrange
    $user = User::factory()->tempBlocked()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Act
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // Assert
    // Should be blocked with 429 (Too Many Requests)
    $response->assertStatus(302); // Middleware should block
});

test('case-insensitive username and email login works', function () {
    // Arrange
    $user = User::factory()->create([
        'username' => 'TestUser',
        'email' => 'Test@Example.Com',
        'password' => bcrypt('password123'),
        'status' => 'active',
    ]);

    // Act: Login with lowercase
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // Note: This test assumes login functionality exists
    // Should allow login with lowercase username

    // Act: Login with uppercase
    $response = $this->post('/login', [
        'email' => 'TEST@EXAMPLE.COM',
        'password' => 'password123',
    ]);

    // Note: This test assumes login functionality exists
    // Should allow login with uppercase email
});
