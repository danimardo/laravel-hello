<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('security headers are present on all responses', function () {
    // Act: Make a request to a public route
    $response = $this->get('/login');

    // Assert: Security headers are present
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Content-Security-Policy');
    $response->assertHeader('Permissions-Policy');
});

test('security headers are present on authenticated routes', function () {
    // Arrange: Create user
    $user = \App\Models\User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Act: Login and access protected route
    $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
    ]);

    $response = $this->get('/counter');

    // Assert: Security headers are present
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
});

test('admin routes have security headers', function () {
    // Arrange: Create admin
    $admin = \App\Models\User::factory()->admin()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    // Act: Login and access admin route
    $this->post('/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $response = $this->get('/admin/users');

    // Assert: Security headers are present
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Content-Security-Policy');
});

test('server header is removed', function () {
    // Act: Make a request
    $response = $this->get('/login');

    // Assert: Server header is not present or doesn't expose version
    if ($response->headers->has('Server')) {
        $server = $response->headers->get('Server');
        expect($server)->not->toContain('Apache');
        expect($server)->not->toContain('Nginx');
        expect($server)->not->toContain('Laravel');
    }
});

test('content security policy is properly configured', function () {
    // Act: Make a request
    $response = $this->get('/login');

    // Assert: CSP is present and well-formed
    $csp = $response->headers->get('Content-Security-Policy');

    expect($csp)->not->toBeNull();
    expect($csp)->toContain("default-src 'self'");
    expect($csp)->toContain("style-src 'self'");
    expect($csp)->toContain("script-src 'self'");
    expect($csp)->toContain("frame-ancestors 'none'");
});

test('clickjacking protection prevents embedding', function () {
    // Act: Make a request
    $response = $this->get('/login');

    // Assert: X-Frame-Options prevents embedding
    $response->assertHeader('X-Frame-Options', 'DENY');
});

test('mime sniffing protection is enabled', function () {
    // Act: Make a request
    $response = $this->get('/login');

    // Assert: X-Content-Type-Options prevents sniffing
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('xss protection header is set', function () {
    // Act: Make a request
    $response = $this->get('/login');

    // Assert: X-XSS-Protection is enabled
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
});

test('referrer policy protects privacy', function () {
    // Act: Make a request
    $response = $this->get('/login');

    // Assert: Referrer-Policy is configured
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('permissions policy restricts sensitive features', function () {
    // Act: Make a request
    $response = $this->get('/login');

    // Assert: Permissions-Policy restricts sensitive features
    $permissions = $response->headers->get('Permissions-Policy');

    expect($permissions)->not->toBeNull();
    expect($permissions)->toContain('geolocation=()');
    expect($permissions)->toContain('camera=()');
    expect($permissions)->toContain('microphone=()');
});
