<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('user can change password successfully', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and change password
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'oldpassword123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    // Assert: Password changed successfully
    $response->assertRedirect();
    $response->assertSessionHas('success', 'Contraseña actualizada correctamente');

    // Assert: Old password no longer works
    $this->post('/logout');
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'oldpassword123',
    ])->assertSessionHasErrors();

    // Assert: New password works
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'newpassword123',
    ]);
    $response->assertRedirect();
});

test('user cannot change password with wrong current password', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('correctpassword123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and try to change password with wrong current password
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'correctpassword123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'wrongpassword',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    // Assert: Shows error
    $response->assertRedirect();
    $response->assertSessionHasErrors('current_password', 'Contraseña actual incorrecta');
});

test('user cannot change password without authentication', function () {
    // Arrange: No user logged in

    // Act: Try to change password
    $response = $this->post('/change-password', [
        'current_password' => 'somepassword',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    // Assert: Redirected to login
    $response->assertRedirect('/login');
});

test('user cannot change password with mismatched confirmation', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and try to change password with mismatched confirmation
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'oldpassword123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'differentpassword',
    ]);

    // Assert: Validation error
    $response->assertSessionHasErrors('new_password');
});

test('user cannot change password with too short new password', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and try to change password with too short password
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'oldpassword123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'short',
        'new_password_confirmation' => 'short',
    ]);

    // Assert: Validation error for minimum length
    $response->assertSessionHasErrors('new_password');
});

test('user cannot change password without uppercase', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and try to change password without uppercase
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'oldpassword123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'weakpassword1!',
        'new_password_confirmation' => 'weakpassword1!',
    ]);

    // Assert: Validation error for missing uppercase
    $response->assertSessionHasErrors('new_password');
});

test('user cannot change password without lowercase', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and try to change password without lowercase
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'oldpassword123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'WEAKPASSWORD1!',
        'new_password_confirmation' => 'WEAKPASSWORD1!',
    ]);

    // Assert: Validation error for missing lowercase
    $response->assertSessionHasErrors('new_password');
});

test('user cannot change password without number', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and try to change password without number
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'oldpassword123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'WeakPassword!',
        'new_password_confirmation' => 'WeakPassword!',
    ]);

    // Assert: Validation error for missing number
    $response->assertSessionHasErrors('new_password');
});

test('user cannot change password without special character', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and try to change password without special character
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'oldpassword123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'WeakPassword1',
        'new_password_confirmation' => 'WeakPassword1',
    ]);

    // Assert: Validation error for missing special character
    $response->assertSessionHasErrors('new_password');
});

test('user can change password with all complexity requirements', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and change password with all complexity requirements
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'oldpassword123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'StrongPass1!',
        'new_password_confirmation' => 'StrongPass1!',
    ]);

    // Assert: Password changed successfully
    $response->assertRedirect();
    $response->assertSessionHas('success', 'Contraseña actualizada correctamente');
});

test('guest can see change password page but gets redirected', function () {
    // Arrange: No user logged in

    // Act: Visit change password page
    $response = $this->get('/change-password');

    // Assert: Redirected to login
    $response->assertRedirect('/login');
});

test('authenticated user can see change password page', function () {
    // Arrange: Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and visit change password page
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    $response = $this->get('/change-password');

    // Assert: Page loads successfully
    $response->assertOk();
    $response->assertSee('Cambiar Contraseña');
});

test('admin can change password', function () {
    // Arrange: Create admin
    $admin = User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('adminold123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    // Act: Login and change password
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'adminold123',
    ]);

    $response = $this->post('/change-password', [
        'current_password' => 'adminold123',
        'new_password' => 'adminnew123',
        'new_password_confirmation' => 'adminnew123',
    ]);

    // Assert: Password changed successfully
    $response->assertRedirect();
    $response->assertSessionHas('success', 'Contraseña actualizada correctamente');
});
