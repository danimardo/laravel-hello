# Quick Start Guide: Laravel Counter PoC
# Autenticación y Gestión de Usuarios
# Date: 2026-01-11

## Overview

Esta guía te ayudará a configurar y ejecutar la aplicación Laravel Counter PoC en tu entorno local. La aplicación implementa un sistema completo de autenticación con roles, un contador interactivo y gestión de usuarios administrativos.

## Prerequisites

### Required Software

- **PHP**: >= 8.4
- **Composer**: >= 2.6
- **Node.js**: >= 20.x
- **NPM**: >= 10.x
- **Docker**: >= 20.x (para Laravel Sail)
- **Git**: Para clonar el repositorio

### Database

- **MariaDB**: >= 10.11
- **Host**: 192.168.1.226
- **Port**: 3306

## Installation Steps

### 1. Clone Repository

```bash
git clone <repository-url>
cd laravel-hello
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
# Copiar archivo de entorno
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 5. Configure Database (.env)

```env
DB_CONNECTION=mariadb
DB_HOST=192.168.1.226
DB_PORT=3306
DB_DATABASE=laravel_counter_poc
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Session Configuration (2 horas)
SESSION_LIFETIME=120

# Cache Configuration
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 6. Create Database

```sql
CREATE DATABASE laravel_counter_poc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 7. Run Migrations

```bash
php artisan migrate

# O si usas Docker Sail:
./vendor/bin/sail artisan migrate
```

### 8. Seed Database

```bash
# Crear usuario admin inicial
php artisan db:seed --class=AdminUserSeeder

# O usar el seeder completo:
php artisan db:seed
```

**Credenciales por defecto del admin**:
- Username: `admin`
- Email: `admin@example.com`
- Password: `Admin123!` (⚠️ CAMBIAR EN PRIMER LOGIN)

### 9. Build Assets

```bash
# Para desarrollo
npm run dev

# Para producción
npm run build
```

### 10. Create Storage Link

```bash
php artisan storage:link
```

## Running the Application

### Option 1: PHP Built-in Server (Development)

```bash
php artisan serve
# Acceder: http://localhost:8000
```

### Option 2: Laravel Sail (Docker)

```bash
# Iniciar contenedores
./vendor/bin/sail up -d

# Ver logs
./vendor/bin/sail logs

# Detener
./vendor/bin/sail down
```

### Option 3: XAMPP/WAMP

Configura tu servidor web para apuntar al directorio `public/`

## First Login

### 1. Acceso Inicial

1. Navegar a `/login`
2. Usar credenciales del admin:
   - Username: `admin`
   - Password: `Admin123!`
3. **OBLIGATORIO**: Cambiar contraseña en `/change-password`

### 2. Verificar Acceso

- **Admin**: Debe acceder a `/counter` y `/admin/users`
- **User**: Solo debe acceder a `/counter`

## Testing

### Run Tests

```bash
# Todos los tests
php artisan test

# Con coverage (genera reporte HTML)
php artisan test --coverage --coverage-html coverage/html

# Con coverage (formato texto)
php artisan test --coverage

# Solo tests específicos
php artisan test --filter=Auth
php artisan test --filter=UserManagement
php artisan test --filter=Counter
php artisan test --filter=SessionTimeout
php artisan test --filter=ChangePassword
php artisan test --filter=RoleMiddleware
```

### Test Coverage Analysis

```bash
# Ejecutar script automatizado (objetivo: 80%)
./scripts/run-coverage-analysis.sh

# Ver resultados HTML
open coverage/html/index.html  # macOS
xdg-open coverage/html/index.html  # Linux

# Ver reporte en texto
cat coverage/coverage.txt
```

### Test Database

```bash
# Ejecutar tests en memoria (SQLite)
php artisan test --env=testing

# O configurar .env.testing con SQLite
```

### Nuevos Tests Implementados

#### Authentication & Security
- **ChangePasswordTest**: Validación de cambio de contraseña y complejidad
- **SessionTimeoutTest**: Expiración automática de sesión (2 horas)
- **RateLimitTest**: Rate limiting en login (5 intentos/hora)

#### Access Control
- **RoleMiddlewareTest**: Validación de roles (admin/user)
- **UserAccessTest**: Restricciones por rol en rutas

#### Admin Panel
- **UserManagementTest**: CRUD de usuarios
- **AdminProtectionTest**: Protección del usuario admin

#### Integration Tests
- **UserFlowTest**: Flujo completo de usuario
- **AdminFlowTest**: Flujo completo de administración
- **UserAccessTest**: Control de acceso integral

## User Flows

### 1. Login Flow

```
Visitante → /login → (credenciales válidas) → /counter
Visitante → /login → (credenciales inválidas) → Error genérico
Visitante → / → (autenticado) → /counter
Visitante → / → (no autenticado) → /login
```

### 2. Counter Flow

```
Usuario autenticado → /counter
        ↓
    [Ver valor actual]
        ↓
    [Incrementar +1]
        ↓
    [Decrementar -1]
        ↓
    [Resetear a 0]
        ↓
    [Logout] → Sesión termina, contador se pierde
```

### 3. Admin User Management Flow

```
Admin → /admin/users
        ↓
    [Ver listado usuarios]
        ↓
    [Crear usuario nuevo]
        ↓
    [Editar usuario existente]
        ↓
    [Desactivar usuario]
        ↓
    [Reactivar usuario]
```

## Key Features

### Authentication

✅ **Login dual**: Username o email en un solo campo
✅ **Case-insensitive**: Usuario `Admin` = `admin`
✅ **Rate limiting**: Máximo 5 intentos por hora
✅ **Secure**: Contraseñas hasheadas, CSRF protection

### Roles & Permissions

✅ **User**: Solo acceso a contador
✅ **Admin**: Acceso a contador + gestión usuarios
✅ **Protected admin**: Usuario `admin` no modificable

### Counter

✅ **Session-based**: No se guarda en DB
✅ **Real-time**: Actualizaciones con Livewire
✅ **Persistent**: Mantiene valor durante la sesión
✅ **Reset**: Se pierde al cerrar sesión

## Common Tasks

### Create New User (Admin)

```php
# Via interfaz web: /admin/users/create
# Via Tinker:
php artisan tinker
>>> User::create([
...     'username' => 'nuevo_usuario',
...     'email' => 'nuevo@ejemplo.com',
...     'password' => Hash::make('Password123!'),
...     'role' => 'user',
... ]);
```

### Reset Counter (User)

```php
# El contador se resetea automáticamente al cerrar sesión
# O manualmente en la interfaz
```

### Block/Unblock User (Admin)

```php
# Automático por intentos fallidos
# Manual via admin panel: /admin/users/{id}/edit
```

### Change Password (Any User)

```php
# Via interfaz: /change-password
# Debe cumplir:
# - Mínimo 8 caracteres
# - 1 mayúscula
# - 1 minúscula
# - 1 número
# - 1 carácter especial

# Nuevo: Indicador visual de fortaleza de contraseña
# - Valida en tiempo real mientras escribes
# - Muestra checklist de requisitos
```

### Session Management

```php
# Timeout automático: 2 horas de inactividad
# Logout automático y redirección a login

# Verificar en: config/session.php
SESSION_LIFETIME=120  # minutos

# Logs de actividad:
tail -f storage/logs/laravel.log
```

## Troubleshooting

### Login Not Working

```bash
# Verificar sesión
php artisan session:table
php artisan migrate

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Database Connection Failed

```bash
# Verificar .env
cat .env | grep DB_

# Test conexión
php artisan tinker
>>> DB::connection()->getPdo();
```

### Permission Denied

```bash
# Verificar permisos
chmod -R 755 .
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Para Windows, usar permisos apropiados
```

### CSRF Token Mismatch

```html
<!-- Verificar que @csrf esté presente en formularios -->
<form method="POST">
    @csrf
    ...
</form>
```

### Livewire Not Updating

```bash
# Re-optimizar autoload
composer dump-autoload

# Limpiar cache de Livewire
php artisan livewire:discover --clean
```

### Assets Not Loading

```bash
# Rebuild assets
npm run build
npm run dev

# Verificar storage link
php artisan storage:link
```

## Development Commands

```bash
# Serve application
php artisan serve

# Watch assets (Hot reload)
npm run dev

# Create migration
php artisan make:migration create_users_table

# Create seeder
php artisan make:seeder AdminUserSeeder

# Create controller
php artisan make:controller AuthController

# Create Livewire component
php artisan make:livewire CounterLivewire

# Tinker (Interactive PHP)
php artisan tinker

# Clear all caches
php artisan optimize:clear

# List routes
php artisan route:list
```

## Production Deployment

### 1. Environment

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
```

### 2. Optimize

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 3. Build Assets

```bash
npm ci --only=production
npm run build
```

### 4. Database

```bash
php artisan migrate --force
php artisan db:seed --force --class=AdminUserSeeder
```

### 5. Permissions

```bash
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

## Security Checklist

- [ ] Cambiar contraseña por defecto del admin
- [ ] Configurar HTTPS en producción
- [ ] Verificar que `APP_ENV=production`
- [ ] Configurar firewall para MariaDB (solo puerto 3306 desde app server)
- [ ] Actualizar dependencias regularmente
- [ ] Configurar backups automáticos de DB
- [ ] Revisar logs de seguridad
- [ ] Verificar configuración de sesión (2 horas)
- [ ] Confirmar rate limiting activo

## Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Livewire Documentation**: https://livewire.laravel.com/docs
- **Tailwind CSS**: https://tailwindcss.com/docs
- **daisyUI**: https://daisyui.com/docs

## Support

Para reportar issues o solicitar funcionalidades, crear un issue en el repositorio.

---

**Versión**: 1.0.0 | **Última actualización**: 2026-01-11
