# Data Model: Laravel Counter PoC

**Feature**: Autenticación y Gestión de Usuarios
**Date**: 2026-01-11
**Spec**: `/specs/001-laravel-counter-auth/spec.md`

## Overview

Este documento define el modelo de datos para la aplicación Laravel Counter PoC. El modelo incluye tres entidades principales: Usuario, Sesión y Contador (por sesión).

## Entities

### 1. User (Usuario)

**Table**: `users`

**Purpose**: Almacena información de usuarios autenticados del sistema.

**Fields**:
- `id` (bigint, PK, auto-increment)
- `username` (string, 255, unique, case-insensitive, not null)
- `email` (string, 255, unique, case-insensitive, not null)
- `password` (string, 255, hashed, not null)
- `role` (enum: 'user' | 'admin', not null, default: 'user')
- `status` (enum: 'active' | 'blocked' | 'inactive', not null, default: 'active')
- `failed_attempts` (integer, default: 0) - Contador de intentos fallidos
- `last_failed_attempt` (timestamp, nullable) - Último intento fallido
- `blocked_until` (timestamp, nullable) - Bloqueo temporal hasta
- `created_at` (timestamp, nullable)
- `updated_at` (timestamp, nullable)

**Constraints**:
- Username y email DEBEN ser únicos (case-insensitive)
- Username y email DEBEN ser normalizados (trim espacios)
- El usuario con username='admin' NO puede ser eliminado
- El rol del usuario con username='admin' NO puede ser modificado
- Si `failed_attempts >= 5` y `last_failed_attempt` < 1 hora → `status='blocked'`
- Si `status='blocked'` y `blocked_until` < now() → desbloqueo automático

**State Transitions**:
```
active → blocked (5 intentos fallidos en 1h)
blocked → active (después de 1 hora o reset admin)
active → inactive (desactivado por admin)
inactive → active (reactivado por admin)
```

**Indexes**:
- Primary: `id`
- Unique: `username` (case-insensitive)
- Unique: `email` (case-insensitive)
- Index: `role`
- Index: `status`

### 2. Session (Sesión)

**Table**: `sessions` (Laravel sessions table)

**Purpose**: Almacena información de sesiones de usuario activas.

**Fields**:
- `id` (string, PK) - Session ID único
- `user_id` (bigint, FK → users.id, not null)
- `ip_address` (string, 45, nullable)
- `user_agent` (text, nullable)
- `payload` (longtext, not null) - Datos serializados de la sesión
- `last_activity` (integer, not null) - Timestamp de última actividad

**Constraints**:
- Cada sesión DEBE tener un usuario asociado
- Sesiones expiran automáticamente después de 2 horas de inactividad
- Si el usuario es desactivado → todas sus sesiones activas son terminadas

**Lifecycle**:
- Creada: al hacer login exitoso
- Actualizada: en cada request con usuario autenticado
- Eliminada: en logout o expiración (2h inactividad)

**Indexes**:
- Primary: `id`
- Foreign: `user_id`
- Index: `last_activity`

### 3. Counter (Contador)

**Storage**: Session data (no database table)

**Purpose**: Mantiene el valor del contador por sesión de usuario.

**Session Key**: `counter`

**Fields**:
- `value` (integer, default: 0) - Valor actual del contador
- `updated_at` (timestamp) - Última modificación

**Constraints**:
- El contador NO se persiste en base de datos
- El contador se pierde al cerrar sesión
- Valores negativos están permitidos
- Si el estado es inválido → reset automático a 0

**State Transitions**:
```
null → 0 (nueva sesión)
0 → 1 (incremento)
0 → -1 (decremento)
any → 0 (reset)
```

## Relationships

```
User (1) ──── (N) Session
  │
  └── Contador (almacenado en sesión, no en DB)

User.role ∈ {user, admin}
  - user: solo acceso a /counter
  - admin: acceso a /counter + /admin/users
```

## Database Configuration

### Collation

**Recommended**: `utf8mb4_unicode_ci` (case-insensitive)

Para garantizar unicidad case-insensitive sin necesidad de campos adicionales.

### MariaDB Settings

- Version: 10.11+
- Connection: 192.168.1.226:3306
- Character Set: utf8mb4
- Collation: utf8mb4_unicode_ci

## Validation Rules

### User Model

```php
// Username
- Required
- String
- Between 3-255 characters
- Unique (case-insensitive)
- No leading/trailing spaces

// Email
- Required
- Valid email format
- Unique (case-insensitive)
- No leading/trailing spaces

// Password
- Required (on create)
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number
- At least 1 special character

// Role
- Required
- Enum: 'user' | 'admin'
- Default: 'user'

// Status
- Required
- Enum: 'active' | 'blocked' | 'inactive'
- Default: 'active'
```

### Session Management

```php
// Lifetime
- 2 hours of inactivity
- Configured in config/session.php
- lifespan = 120 minutes

// Cleanup
- Expired sessions deleted on garbage collection
- Config: session.gc_probability / session.gc_divisor
```

### Counter

```php
// Value
- Integer (positive, negative, or zero)
- No decimal places
- No maximum limit specified
- Stored in session, not database

// Actions
- increment: value + 1
- decrement: value - 1
- reset: value = 0
```

## Security Considerations

1. **Password Storage**: Bcrypt hash (Laravel Hash facade)
2. **Session Security**:
   - Secure cookie flags (httponly, secure, same_site)
   - Regenerate session ID after login
   - Invalidate session on logout
3. **CSRF Protection**: All forms protected with @csrf token
4. **SQL Injection**: Prevented by Eloquent ORM and Query Builder
5. **XSS Prevention**: Blade escaping enabled by default

## Migrations

### Migration 1: Create Users Table

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('username')->unique();
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->enum('role', ['user', 'admin'])->default('user');
    $table->enum('status', ['active', 'blocked', 'inactive'])->default('active');
    $table->integer('failed_attempts')->default(0);
    $table->timestamp('last_failed_attempt')->nullable();
    $table->timestamp('blocked_until')->nullable();
    $table->rememberToken();
    $table->timestamps();
});
```

### Migration 2: Create Sessions Table

```php
Schema::create('sessions', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->longText('payload');
    $table->integer('last_activity');
    $table->index(['user_id']);
    $table->index(['last_activity']);
});
```

### Migration 3: Update Users Table (Case-Insensitive)

```php
// Add case-insensitive unique indexes
DB::statement('CREATE UNIQUE INDEX users_username_unique_ci ON users (LOWER(username))');
DB::statement('CREATE UNIQUE INDEX users_email_unique_ci ON users (LOWER(email))');

// Or use collations (preferred)
$table->string('username')->unique('users_username_unique_ci');
$table->string('email')->unique('users_email_unique_ci');
```

## Seeders

### AdminUserSeeder

```php
public function run(): void
{
    User::create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('Admin12345*'), // Must be changed on first login
        'role' => 'admin',
        'status' => 'active',
    ]);
}
```

## Indexes Strategy

### Users Table
- Primary: `id`
- Unique: `LOWER(username)` (case-insensitive)
- Unique: `LOWER(email)` (case-insensitive)
- Index: `role`
- Index: `status`
- Index: `failed_attempts`
- Index: `last_failed_attempt`

### Sessions Table
- Primary: `id`
- Foreign: `user_id`
- Index: `last_activity` (for GC)

## Performance Considerations

1. **User Lookups**: Indexed by username/email for fast auth
2. **Session Cleanup**: Indexed by last_activity for garbage collection
3. **Admin Queries**: Indexed by role for admin user lookups
4. **Rate Limiting**: Check failed_attempts without full table scan

## Backup & Recovery

- **User Data**: Critical - daily backups
- **Sessions**: Non-critical - can be lost without major impact
- **Counter**: Session-bound - always lost on logout

**Recovery Strategy**:
- Users: Restore from DB backup
- Sessions: Expired sessions will recreate naturally
- Counter: Always reset on new session
