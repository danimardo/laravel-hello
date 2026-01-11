<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('admin can view user list', function () {
    // Arrange: Create admin and regular users
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    User::factory()->count(5)->create();

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Assert: Admin can access user management
    $response = $this->get('/admin/users');
    $response->assertOk();
    $response->assertSee('Gestión de Usuarios');
});

test('admin can create new user', function () {
    // Arrange: Create admin
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Create new user
    $response = $this->post('/admin/users', [
        'username' => 'newuser',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ]);

    // Assert: User was created
    $response->assertRedirect('/admin/users');
    $this->assertDatabaseHas('users', [
        'username' => 'newuser',
        'email' => 'newuser@example.com',
        'role' => 'user',
        'status' => 'active',
    ]);
});

test('admin can edit user', function () {
    // Arrange: Create admin and regular user
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Update user
    $response = $this->put('/admin/users/' . $user->id, [
        'username' => 'updateduser',
        'email' => 'updated@example.com',
        'role' => 'admin',
    ]);

    // Assert: User was updated
    $response->assertRedirect('/admin/users');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'username' => 'updateduser',
        'email' => 'updated@example.com',
        'role' => 'admin',
    ]);
});

test('admin cannot modify admin user', function () {
    // Arrange: Create admin
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Try to update admin user
    $response = $this->put('/admin/users/' . $admin->id, [
        'username' => 'modifiedadmin',
        'email' => 'modified@example.com',
        'role' => 'user',
    ]);

    // Assert: Should fail with error
    $response->assertSessionHasErrors('admin');
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'username' => 'admin',
        'role' => 'admin',
    ]);
});

test('admin can deactivate user', function () {
    // Arrange: Create admin and regular user
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'status' => 'active',
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Deactivate user
    $response = $this->patch('/admin/users/' . $user->id . '/deactivate');

    // Assert: User was deactivated
    $response->assertRedirect('/admin/users');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'status' => 'inactive',
    ]);
});

test('admin can activate user', function () {
    // Arrange: Create admin and inactive user
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'status' => 'inactive',
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Activate user
    $response = $this->patch('/admin/users/' . $user->id . '/activate');

    // Assert: User was activated
    $response->assertRedirect('/admin/users');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'status' => 'active',
    ]);
});

test('admin cannot deactivate admin user', function () {
    // Arrange: Create admin
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Try to deactivate admin
    $response = $this->patch('/admin/users/' . $admin->id . '/deactivate');

    // Assert: Should fail with error
    $response->assertSessionHasErrors('admin');
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'status' => 'active',
    ]);
});

test('regular user cannot access admin panel', function () {
    // Arrange: Create regular user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
    ]);

    // Act: Login as regular user
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    // Try to access admin panel
    $response = $this->get('/admin/users');

    // Assert: Should be forbidden (403)
    $response->assertStatus(403);
});

test('guest cannot access admin panel', function () {
    // Arrange: No user

    // Act: Try to access admin panel

    // Assert: Should be redirected to login
    $response = $this->get('/admin/users');
    $response->assertRedirect('/login');
});

test('admin can delete user', function () {
    // Arrange: Create admin and regular user
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Delete user
    $response = $this->delete('/admin/users/' . $user->id);

    // Assert: User was deactivated (soft delete)
    $response->assertRedirect('/admin/users');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'status' => 'inactive',
    ]);
});

test('admin cannot delete admin user', function () {
    // Arrange: Create admin
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Try to delete admin
    $response = $this->delete('/admin/users/' . $admin->id);

    // Assert: Should fail with error
    $response->assertSessionHasErrors('admin');
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'status' => 'active',
    ]);
});

test('case-insensitive username and email validation works', function () {
    // Arrange: Create admin
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
    ]);

    // Act: Login as admin
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    // Try to create user with existing username (different case)
    $response = $this->post('/admin/users', [
        'username' => 'ADMIN', // Same as admin but different case
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ]);

    // Assert: Should fail validation
    $response->assertSessionHasErrors('username');

    // Try to create user with existing email (different case)
    $response = $this->post('/admin/users', [
        'username' => 'newuser',
        'email' => 'ADMIN@EXAMPLE.COM', // Same as admin@example.com but different case
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ]);

    // Assert: Should fail validation
    $response->assertSessionHasErrors('email');
});
