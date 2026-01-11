<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('active session allows access to protected routes', function () {
    // Arrange: Create user and login
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and access counter with recent activity
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // Simulate recent activity (5 minutes ago)
    session(['last_activity_time' => now()->subMinutes(5)->timestamp]);

    $response = $this->get('/counter');

    // Assert: Can access counter
    $response->assertOk();
    $response->assertSee('Contador');
});

test('expired session redirects to login', function () {
    // Arrange: Create user and login
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // Simulate expired session (3 hours ago)
    session(['last_activity_time' => now()->subHours(3)->timestamp]);

    $response = $this->get('/counter');

    // Assert: Redirected to login with error message
    $response->assertRedirect('/login');
    $response->assertSessionHas('error', 'Su sesión ha expirado después de 2 horas de inactividad');
});

test('session activity updates on each request', function () {
    // Arrange: Create user and login
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and make requests
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // First request
    $this->get('/counter');
    $firstActivityTime = session('last_activity_time');

    // Wait a moment
    sleep(1);

    // Second request
    $this->get('/counter');
    $secondActivityTime = session('last_activity_time');

    // Assert: Activity time was updated
    expect($secondActivityTime)->toBeGreaterThan($firstActivityTime);
});

test('expired session returns JSON error for API requests', function () {
    // Arrange: Create user and login
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // Simulate expired session
    session(['last_activity_time' => now()->subHours(3)->timestamp]);

    // Request with JSON accept header
    $response = $this->withHeaders([
        'Accept' => 'application/json',
    ])->get('/counter');

    // Assert: Returns JSON error
    $response->assertStatus(401);
    $response->assertJson([
        'success' => false,
        'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.',
    ]);
});

test('logout clears session and activity time', function () {
    // Arrange: Create user and login
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // Verify session has activity time
    expect(session('last_activity_time'))->not->toBeNull();

    // Logout
    $this->post('/logout');

    // Assert: Activity time is cleared
    expect(session('last_activity_time'))->toBeNull();
});

test('guest user can access public routes without session check', function () {
    // Arrange: No user logged in

    // Act: Access public routes
    $response = $this->get('/login');

    // Assert: Can access login page
    $response->assertOk();
    $response->assertSee('Iniciar Sesión');
});

test('admin session also respects timeout', function () {
    // Arrange: Create admin
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Simulate expired session
    session(['last_activity_time' => now()->subHours(3)->timestamp]);

    $response = $this->get('/admin/users');

    // Assert: Admin session also expired
    $response->assertRedirect('/login');
});
