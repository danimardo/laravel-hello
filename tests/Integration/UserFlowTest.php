<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('full user flow: login, use counter, logout', function () {
    // Arrange: Create a user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act & Assert: User visits home page and is redirected to login
    $response = $this->get('/');
    $response->assertRedirect('/login');

    // Act & Assert: Login with valid credentials
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);
    $response->assertRedirect('/counter');

    // Assert: User is authenticated
    $this->assertAuthenticatedAs($user);

    // Assert: Counter is accessible and shows initial value (0)
    $response = $this->get('/counter');
    $response->assertOk();
    $response->assertSee('Contador');
    $response->assertSee('0');

    // Note: This test assumes Livewire integration works
    // In a real scenario, we would test counter interactions here
    // For now, we verify the page loads correctly

    // Act & Assert: Logout
    $response = $this->post('/logout');
    $response->assertRedirect('/login');

    // Assert: User is not authenticated
    $this->assertGuest();
});

test('admin user can access counter and admin features', function () {
    // Arrange: Create an admin user
    $admin = User::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    // Act & Assert: Login as admin
    $response = $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);
    $response->assertRedirect('/counter');

    // Assert: User is authenticated as admin
    $this->assertAuthenticatedAs($admin);
    expect(Auth()->user()->isAdmin())->toBeTrue();

    // Assert: Admin can see admin features in UI
    $response = $this->get('/counter');
    $response->assertOk();
    $response->assertSee('Administrar Usuarios');
});

test('case-insensitive login works', function () {
    // Arrange: Create a user
    $user = User::factory()->create([
        'username' => 'TestUser',
        'email' => 'Test@Example.Com',
        'password' => bcrypt('password123'),
        'status' => 'active',
    ]);

    // Act & Assert: Login with lowercase username
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);
    $response->assertRedirect('/counter');
    $this->assertAuthenticatedAs($user);

    // Logout
    $this->post('/logout');

    // Act & Assert: Login with uppercase email
    $response = $this->post('/login', [
        'email' => 'TEST@EXAMPLE.COM',
        'password' => 'password123',
    ]);
    $response->assertRedirect('/counter');
    $this->assertAuthenticatedAs($user);
});

test('invalid login shows error and does not authenticate', function () {
    // Act & Assert: Login with wrong password
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'wrongpassword',
    ]);
    $response->assertRedirect();
    $response->assertSessionHasErrors('login');

    // Assert: User is not authenticated
    $this->assertGuest();
});

test('inactive user cannot login', function () {
    // Arrange: Create inactive user
    $user = User::factory()->create([
        'username' => 'inactiveuser',
        'email' => 'inactive@example.com',
        'password' => bcrypt('password123'),
        'status' => 'inactive',
    ]);

    // Act & Assert: Login fails
    $response = $this->post('/login', [
        'username' => 'inactiveuser',
        'password' => 'password123',
    ]);

    // Should be blocked (302 redirect with error)
    $response->assertRedirect();
    $this->assertGuest();
});

test('temp blocked user cannot login', function () {
    // Arrange: Create temporarily blocked user
    $user = User::factory()->create([
        'username' => 'blockeduser',
        'email' => 'blocked@example.com',
        'password' => bcrypt('password123'),
        'status' => 'temp_blocked',
        'locked_until' => now()->addHour(),
    ]);

    // Act & Assert: Login is blocked
    $response = $this->post('/login', [
        'username' => 'blockeduser',
        'password' => 'password123',
    ]);

    // Should be blocked by middleware
    $response->assertRedirect();
    $this->assertGuest();
});

test('guest cannot access counter page', function () {
    // Act & Assert: Guest is redirected to login
    $response = $this->get('/counter');
    $response->assertRedirect('/login');
});

test('authenticated user can access counter', function () {
    // Arrange: Create and authenticate user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'status' => 'active',
    ]);

    $this->actingAs($user);

    // Act & Assert: User can access counter
    $response = $this->get('/counter');
    $response->assertOk();
    $response->assertSee('Contador');
});

test('user session persists across requests', function () {
    // Arrange: Create and authenticate user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'status' => 'active',
    ]);

    // Act & Assert: Login and verify session
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);
    $response->assertRedirect('/counter');

    // Make multiple requests to verify session persists
    $this->get('/counter')->assertOk();
    $this->get('/counter')->assertOk();

    // Verify user is still authenticated
    expect(Auth()->check())->toBeTrue();
    expect(Auth()->user()->id)->toBe($user->id);
});
