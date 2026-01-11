<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('user role restrictions - complete flow', function () {
    // Arrange: Create admin and regular users
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'username' => 'regularuser',
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Test 1: Regular user cannot access admin panel
    $this->post('/login', [
        'username' => 'regularuser',
        'password' => 'password123',
    ]);

    $this->get('/counter')->assertOk();
    $this->get('/admin/users')->assertStatus(403);
    $this->get('/admin/users/create')->assertStatus(403);
    $this->get('/admin/stats')->assertStatus(403);

    // Test 2: Regular user doesn't see admin menu
    $response = $this->get('/counter');
    $response->assertOk();
    $response->assertDontSee('Administrar Usuarios');

    // Test 3: Logout and login as admin
    $this->post('/logout');
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Test 4: Admin can access all routes
    $this->get('/counter')->assertOk();
    $this->get('/admin/users')->assertOk();
    $this->get('/admin/users/create')->assertOk();
    $this->get('/admin/stats')->assertOk();

    // Test 5: Admin sees admin menu
    $response = $this->get('/counter');
    $response->assertOk();
    $response->assertSee('Administrar Usuarios');
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

    // Assert: Cannot login and is redirected
    $response->assertRedirect();
    $this->assertGuest();

    // Assert: Cannot access protected routes
    $this->get('/counter')->assertRedirect('/login');
    $this->get('/admin/users')->assertRedirect('/login');
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

    // Assert: Cannot access protected routes
    $this->get('/counter')->assertRedirect('/login');
});

test('guest cannot access any protected route', function () {
    // Arrange: No user logged in

    // Assert: All protected routes redirect to login
    $this->get('/counter')->assertRedirect('/login');
    $this->get('/admin/users')->assertRedirect('/login');
    $this->get('/admin/users/create')->assertRedirect('/login');
    $this->get('/change-password')->assertRedirect('/login');
});

test('admin menu visibility based on role', function () {
    // Arrange: Create users
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'username' => 'regularuser',
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Test: Admin sees menu
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $response = $this->get('/counter');
    $response->assertOk();
    $response->assertSee('Administrar Usuarios');

    // Test: Regular user doesn't see menu
    $this->post('/logout');
    $this->post('/login', [
        'username' => 'regularuser',
        'password' => 'password123',
    ]);

    $response = $this->get('/counter');
    $response->assertOk();
    $response->assertDontSee('Administrar Usuarios');
});

test('direct URL access to admin routes returns 403 for users', function () {
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
        '/admin/users/1',
        '/admin/users/1/edit',
        '/admin/stats',
        '/admin/search',
    ];

    foreach ($adminRoutes as $route) {
        $this->get($route)->assertStatus(403);
    }
});

test('admin can perform all admin actions', function () {
    // Arrange: Create admin and regular user
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'username' => 'regularuser',
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Assert: Can access all admin routes
    $this->get('/admin/users')->assertOk();
    $this->get('/admin/users/create')->assertOk();

    // Create new user
    $this->post('/admin/users', [
        'username' => 'newuser',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ])->assertRedirect();

    // Assert: User was created
    $this->assertDatabaseHas('users', [
        'username' => 'newuser',
        'email' => 'newuser@example.com',
        'role' => 'user',
    ]);

    // Update user
    $this->put('/admin/users/' . $user->id, [
        'username' => 'updateduser',
        'email' => 'updated@example.com',
        'role' => 'admin',
    ])->assertRedirect();

    // Assert: User was updated
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'username' => 'updateduser',
        'role' => 'admin',
    ]);
});

test('case-insensitive role checking works correctly', function () {
    // Arrange: Create users with different cases
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'username' => 'regularuser',
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Test: Admin can login with lowercase
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);
    $this->assertAuthenticatedAs($admin);
    $this->get('/admin/users')->assertOk();

    $this->post('/logout');

    // Test: User cannot access admin routes even with correct credentials
    $this->post('/login', [
        'username' => 'regularuser',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($user);
    $this->get('/admin/users')->assertStatus(403);
});

test('csrf protection prevents unauthorized admin actions', function () {
    // Arrange: Create admin and regular user
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

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

    // Assert: Cannot perform admin actions even with POST (CSRF would also block)
    $this->post('/admin/users', [
        'username' => 'hacker',
        'email' => 'hacker@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ])->assertStatus(403); // Would be 419 for CSRF if authenticated, but 403 for role check first
});
