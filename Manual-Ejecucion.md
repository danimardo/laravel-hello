# 🚀 Manual de Ejecución - Laravel Counter PoC

Este manual está preparado para ejecutar el proyecto usando **Laravel Sail** (entorno Dockerizado).

---

## 📋 Requisitos Previos

Asegúrate de tener instalado:

- **Docker Desktop** (instalado y en ejecución)
- **Git**
- **WSL2** (si estás en Windows)

> 💡 **Nota:** No necesitas tener PHP, Composer o Node.js instalados localmente. Sail lo proporciona todo dentro de contenedores Docker.

---

## 🔧 Instalación Inicial

### 1️⃣ Instalar dependencias PHP

Si es la **primera vez** que ejecutas el proyecto, necesitas instalar las dependencias de Composer antes de poder usar Sail:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

<details>
<summary>💡 Alternativa si tienes Composer instalado localmente</summary>

```bash
composer install
```
</details>

### 2️⃣ Configurar archivo de entorno

Copia el archivo de ejemplo y configúralo:

```bash
cp .env.example .env
```

### 3️⃣ Configurar base de datos en `.env`

Este proyecto usa una **base de datos MySQL externa** (no la local de Sail). Verifica que tu archivo `.env` tenga estos valores:

```env
DB_CONNECTION=mysql
DB_HOST=192.168.1.226
DB_PORT=3306
DB_DATABASE=laravel-hello
DB_USERNAME=root
DB_PASSWORD=Vareta69
```

> 🌐 **Base de datos externa:** Este proyecto está configurado para usar una base de datos MySQL en otro servidor. Asegúrate de que:
> - La base de datos `laravel-hello` existe en el servidor
> - El usuario tiene permisos para conectarse remotamente
> - El puerto 3306 está accesible desde tu máquina de desarrollo

### 4️⃣ Crear alias para Sail (⭐ Muy recomendado)

Para no tener que escribir `./vendor/bin/sail` cada vez, crea un alias:

```bash
alias sail='./vendor/bin/sail'
```

**Para hacerlo permanente**, añádelo a tu archivo de configuración de shell:

<details>
<summary>Bash (~/.bashrc)</summary>

```bash
echo "alias sail='./vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc
```
</details>

<details>
<summary>Zsh (~/.zshrc)</summary>

```bash
echo "alias sail='./vendor/bin/sail'" >> ~/.zshrc
source ~/.zshrc
```
</details>

---

## 🚢 Puesta en Marcha con Sail

### 5️⃣ Construir la imagen de Docker (solo primera vez)

Si es la **primera vez** que levantas el proyecto, necesitas construir la imagen de Docker:

```bash
sail build --no-cache
```

> ⏱️ **Tiempo estimado:** 5-10 minutos. Este proceso instala PHP 8.4+, todas las extensiones necesarias, Node.js, Composer, y más.
>
> 💡 **Solo una vez:** Solo necesitas hacer esto la primera vez o cuando actualices el Dockerfile.

### 6️⃣ Levantar los contenedores Docker

```bash
sail up -d
```

> ⏱️ **Primera ejecución:** Puede tardar unos minutos mientras descarga imágenes de Redis y Mailpit.
>
> 🔍 **Modo debug:** Si quieres ver los logs en tiempo real, usa `sail up` sin el flag `-d`

### 7️⃣ Verificar que los contenedores están corriendo

```bash
sail ps
```

Deberías ver estos contenedores activos:
- ✅ `laravel-app` (Puerto 80 y 5173)
- ✅ `laravel-redis` (Puerto 6379)
- ✅ `laravel-mailpit` (Puertos 1025 y 8025)

### 8️⃣ Generar clave de aplicación

```bash
sail artisan key:generate
```

### 9️⃣ Ejecutar migraciones de base de datos

```bash
sail artisan migrate
```

> 🌐 **Base de datos externa:** Las migraciones se ejecutarán en el servidor MySQL externo (192.168.1.226).

<details>
<summary>🔄 Si necesitas rehacer las migraciones</summary>

```bash
# Eliminar todas las tablas y volver a migrar
sail artisan migrate:fresh

# Con seeders incluidos
sail artisan migrate:fresh --seed
```
</details>

### 🔟 Crear usuario administrador inicial

```bash
sail artisan db:seed --class=AdminUserSeeder
```

**🔑 Credenciales por defecto:**

| Campo | Valor |
|-------|-------|
| Username | `admin` |
| Password | `Admin12345*` |

> ⚠️ **IMPORTANTE:** Cambia la contraseña en tu primer login por seguridad.

### 1️⃣1️⃣ Instalar dependencias de Node.js

```bash
sail npm install
```

### 1️⃣2️⃣ Iniciar el servidor de desarrollo

```bash
sail npm run dev
```

> 🔥 **Hot Reload:** Con este servidor activo, cualquier cambio en archivos CSS, JS o Blade se reflejará automáticamente en el navegador.
>
> 💡 **Deja esta terminal abierta** mientras desarrollas. Abre una nueva terminal para ejecutar otros comandos.

<details>
<summary>🏭 Para compilar assets en producción</summary>

```bash
sail npm run build
```
</details>

---

## 🌐 Acceder a la Aplicación

Una vez que todo esté corriendo:

| Página | URL |
|--------|-----|
| 🏠 Página Principal | http://localhost |
| 🔐 Login | http://localhost/login |
| 📧 Mailpit (emails de prueba) | http://localhost:8025 |

> 💡 **Puerto:** Con Sail, la aplicación corre en el **puerto 80** por defecto (no en el 8000 como con `php artisan serve`).

---

## 🧪 Ejecutar Tests

### Tests básicos

```bash
sail artisan test
```

### Tests con cobertura

```bash
sail artisan test --coverage
```

### Tests con cobertura mínima requerida

```bash
sail artisan test --coverage --min=80
```

### Tests específicos

```bash
# Un solo archivo de test
sail artisan test tests/Feature/Auth/LoginTest.php

# Un test específico
sail artisan test --filter=test_admin_can_login
```

---

## 🛠️ Comandos Útiles de Sail

### 🐳 Gestión de contenedores

| Comando | Descripción |
|---------|-------------|
| `sail up -d` | Iniciar contenedores en segundo plano |
| `sail up` | Iniciar contenedores con logs visibles |
| `sail down` | Detener y eliminar contenedores |
| `sail stop` | Detener contenedores (sin eliminar) |
| `sail restart` | Reiniciar contenedores |
| `sail ps` | Ver estado de los contenedores |

### 📋 Logs y debugging

```bash
# Ver todos los logs en tiempo real
sail logs -f

# Ver logs de un servicio específico
sail logs -f laravel.test
sail logs -f mariadb

# Ver últimas 100 líneas
sail logs --tail=100
```

### 🐚 Acceso a shells

```bash
# Shell del contenedor PHP (recomendado)
sail shell

# Bash del contenedor PHP
sail bash

# Shell root del contenedor
sail root-shell

# Acceder a MariaDB CLI
sail mariadb

# Acceder a Redis CLI
sail redis
```

### 📦 Gestión de dependencias

```bash
# Composer
sail composer install
sail composer update
sail composer require [paquete]
sail composer dump-autoload

# NPM
sail npm install
sail npm update
sail npm run dev
sail npm run build

# Yarn (si lo prefieres)
sail yarn install
sail yarn dev
```

### 🎨 Laravel Artisan

```bash
# Comandos generales
sail artisan list
sail artisan route:list
sail artisan cache:clear
sail artisan config:clear
sail artisan view:clear

# Base de datos
sail artisan migrate
sail artisan migrate:fresh
sail artisan migrate:fresh --seed
sail artisan db:seed

# Tinker (REPL de Laravel)
sail artisan tinker
```

### 🗄️ Base de datos

```bash
# Conectarse a la base de datos externa vía Sail
sail exec laravel.test mysql -h 192.168.1.226 -u root -p
# Contraseña: Vareta69

# Backup de la base de datos externa (desde el servidor directamente)
# Ejecuta esto EN el servidor 192.168.1.226:
mysqldump -u root -p laravel-hello > backup.sql

# Restaurar desde backup (desde el servidor)
mysql -u root -p laravel-hello < backup.sql

# Ver tablas de la base de datos
sail artisan db:show

# Ejecutar consultas SQL directamente
sail artisan tinker
# Luego: DB::select('SELECT * FROM users');
```

### 🧹 Limpieza y mantenimiento

```bash
# Limpiar cachés de Laravel
sail artisan optimize:clear

# Limpiar volúmenes de Docker (⚠️ borra datos de BD)
sail down -v

# Reconstruir contenedores
sail build --no-cache

# Ver espacio usado por Docker
docker system df
```

---

## 🔧 Solución de Problemas Comunes

### ❌ Error: "Port 80 is already in use"

Si tienes otro servicio usando el puerto 80:

```bash
# Cambiar puerto en .env
APP_PORT=8080

# Reiniciar Sail
sail down && sail up -d
```

Luego accede a http://localhost:8080

### ❌ Error: "Cannot connect to Docker daemon"

```bash
# Asegúrate de que Docker Desktop esté corriendo
# En Windows WSL2:
sudo service docker start
```

### ❌ Los cambios no se reflejan

```bash
# Limpiar cachés
sail artisan optimize:clear

# Reiniciar Vite (si estás en desarrollo)
sail npm run dev
```

### ❌ Error de permisos en archivos

```bash
# Arreglar permisos (desde fuera de Sail)
sudo chown -R $USER:$USER .
```

### ❌ Error: "No se puede conectar a la base de datos"

Si obtienes errores al ejecutar migraciones o al conectarte a la base de datos externa:

```bash
# 1. Verifica que Docker puede alcanzar el servidor MySQL
sail exec laravel.test ping 192.168.1.226

# 2. Prueba la conexión MySQL directamente
sail exec laravel.test mysql -h 192.168.1.226 -u root -p
# Contraseña: Vareta69
```

**Posibles causas:**
- ⚠️ El firewall del servidor MySQL bloquea conexiones externas
- ⚠️ MySQL no permite conexiones remotas del usuario root
- ⚠️ La base de datos `laravel-hello` no existe

**Solución:**

En el servidor MySQL (192.168.1.226), ejecuta:

```sql
-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS `laravel-hello` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Dar permisos al usuario root desde cualquier IP
GRANT ALL PRIVILEGES ON `laravel-hello`.* TO 'root'@'%' IDENTIFIED BY 'Vareta69';
FLUSH PRIVILEGES;
```

---

## 📝 Notas Adicionales

### 🎯 Características de Sail

- ✅ **Stack completo:** PHP 8.4+, Redis, Mailpit (MySQL en servidor externo)
- ✅ **Hot reload:** Los cambios en archivos se reflejan instantáneamente
- ✅ **Sin instalación local:** No necesitas PHP, Composer o Node.js en tu sistema
- ✅ **Aislamiento:** Cada proyecto tiene su propio entorno

### 🚦 Workflow de desarrollo diario

```bash
# 1. Iniciar el entorno
sail up -d

# 2. Iniciar el servidor de desarrollo (en otra terminal)
sail npm run dev

# 3. Trabajar normalmente con tu editor

# 4. Ejecutar tests cuando necesites
sail artisan test

# 5. Al terminar (opcional)
sail down
```

### 💾 Persistencia de datos

Los datos de la base de datos persisten entre reinicios de Sail. Para borrarlos completamente:

```bash
sail down -v  # ⚠️ Esto borrará todos los datos de la BD
```

---


### 🌐 Configuración de Base de Datos Externa

Este proyecto está configurado para usar una base de datos MySQL en un servidor externo (`192.168.1.226`), NO la base de datos local de Sail.

**Ventajas:**
- ✅ Datos compartidos entre múltiples entornos
- ✅ Base de datos persistente fuera de Docker
- ✅ Ideal para equipos que trabajan en la misma base de datos

**Configuración actual:**

| Parámetro | Valor |
|-----------|-------|
| Host | `192.168.1.226` |
| Puerto | `3306` |
| Base de datos | `laravel-hello` |
| Usuario | `root` |
| Contraseña | `Vareta69` |

**Importante:**
- 🔒 El servidor MariaDB local está **deshabilitado** en `docker-compose.yml`
- 🔌 Asegúrate de que el servidor MySQL externo esté accesible desde tu red
- 🔐 En producción, usa credenciales más seguras y un usuario con permisos limitados

**Para volver a usar base de datos local:**

Si prefieres usar una base de datos local de Sail:

1. Edita `docker-compose.yml` y descomenta la sección `mariadb`
2. Actualiza `.env`:
   ```env
   DB_CONNECTION=mariadb
   DB_HOST=mariadb
   DB_PORT=3306
   DB_DATABASE=laravel_counter_poc
   DB_USERNAME=sail
   DB_PASSWORD=password
   ```
3. Reinicia los contenedores: `sail down && sail up -d`


---

## 🔗 Enlaces Útiles

- [Documentación de Laravel Sail](https://laravel.com/docs/12.x/sail)
- [Documentación de Laravel 12](https://laravel.com/docs/12.x)
- [Documentación de Livewire 3](https://livewire.laravel.com)
- [Documentación de Tailwind CSS](https://tailwindcss.com)
- [Documentación de daisyUI](https://daisyui.com)

---

**¿Necesitas ayuda?** Revisa la documentación oficial o abre un issue en el repositorio.
