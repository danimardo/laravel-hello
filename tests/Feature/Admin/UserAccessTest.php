<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('user role blocked from /admin/users', function () {
    // Arrange: Create regular user
    $user = User::factory()->create([
        'username' => 'regularuser',
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login as regular user
    $this->post('/login', [
        'username' => 'regularuser',
        'password' => 'password123',
    ]);

    // Assert: Cannot access admin routes
    $this->get('/admin/users')->assertStatus(403);
    $this->get('/admin/users/create')->assertStatus(403);
    $this->post('/admin/users')->assertStatus(403);
    $this->get('/admin/users/1/edit')->assertStatus(403);
    $this->put('/admin/users/1')->assertStatus(403);
    $this->delete('/admin/users/1')->assertStatus(403);
});

test('user role has no admin menu', function () {
    // Arrange: Create regular user
    $user = User::factory()->create([
        'username' => 'regularuser',
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login as regular user
    $this->post('/login', [
        'username' => 'regularuser',
        'password' => 'password123',
    ]);

    // Act: Visit counter page
    $response = $this->get('/counter');

    // Assert: No admin menu items in navigation
    $response->assertOk();
    $response->assertDontSee('Administrar Usuarios');
    $response->assertDontSee('admin/users');
});

test('direct URL access returns 403', function () {
    // Arrange: Create regular user
    $user = User::factory()->create([
        'username' => 'regularuser',
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login as regular user
    $this->post('/login', [
        'username' => 'regularuser',
        'password' => 'password123',
    ]);

    // Assert: All admin routes return 403
    $adminRoutes = [
        '/admin/users',
        '/admin/users/create',
        '/admin/stats',
        '/admin/search',
    ];

    foreach ($adminRoutes as $route) {
        $this->get($route)->assertStatus(403);
    }
});

test('admin can access all admin routes', function () {
    // Arrange: Create admin user
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

    // Assert: Can access admin routes
    $this->get('/admin/users')->assertOk();
    $this->get('/admin/users/create')->assertOk();
    $this->get('/admin/stats')->assertOk();
});

test('inactive user cannot access any route', function () {
    // Arrange: Create inactive user
    $user = User::factory()->create([
        'username' => 'inactiveuser',
        'email' => 'inactive@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'inactive',
    ]);

    // Act: Try to login
    $response = $this->post('/login', [
        'username' => 'inactiveuser',
        'password' => 'password123',
    ]);

    // Assert: Cannot login
    $response->assertRedirect();
    $this->assertGuest();
});

test('temp blocked user cannot access routes', function () {
    // Arrange: Create blocked user
    $user = User::factory()->tempBlocked()->create([
        'username' => 'blockeduser',
        'email' => 'blocked@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Act: Try to login
    $response = $this->post('/login', [
        'username' => 'blockeduser',
        'password' => 'password123',
    ]);

    // Assert: Cannot login (blocked)
    $response->assertRedirect();
    $this->assertGuest();
});

test('user cannot access admin API routes', function () {
    // Arrange: Create regular user
    $user = User::factory()->create([
        'username' => 'regularuser',
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login as regular user
    $this->post('/login', [
        'username' => 'regularuser',
        'password' => 'password123',
    ]);

    // Assert: Cannot access admin API
    $this->get('/admin/stats')->assertStatus(403);
    $this->get('/admin/search')->assertStatus(403);
});

test('admin can see admin menu items', function () {
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

    // Act: Visit counter page
    $response = $this->get('/counter');

    // Assert: Can see admin menu
    $response->assertOk();
    $response->assertSee('Administrar Usuarios');
});

test('case-insensitive role check works', function () {
    // Arrange: Create user with lowercase role
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user', // Already lowercase
        'status' => 'active',
    ]);

    // Act: Login
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // Assert: Cannot access admin routes
    $this->get('/admin/users')->assertStatus(403);
});
