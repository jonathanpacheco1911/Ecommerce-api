## Instalación y configuración

### Requisitos previos

- PHP 8.2+ con extensiones `pdo_mysql`, `mbstring`, `curl`, `openssl`
- Composer
- MySQL 8 (XAMPP, Laragon o instalación independiente)
- Cuenta de Stripe en modo test: https://dashboard.stripe.com/register

### 1. Clonar el repositorio

```bash
git clone https://github.com/jonathanpacheco1911/Ecommerce-api.git
cd Ecommerce-api
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus credenciales:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_api
DB_USERNAME=root
DB_PASSWORD=

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

L5_SWAGGER_CONST_HOST=http://localhost:8000
```

Las llaves de Stripe se obtienen en https://dashboard.stripe.com/test/apikeys. Si usas XAMPP con otro MySQL corriendo en paralelo, revisa el puerto real en el panel de control (puede no ser el 3306 por defecto).

### 4. Crear la base de datos

```bash
mysql -u root -e "CREATE DATABASE ecommerce_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

En Windows con XAMPP, si `mysql` no se reconoce como comando, créala desde phpMyAdmin (botón "Admin" en el panel de XAMPP) o usa la ruta completa `C:\xampp\mysql\bin\mysql.exe`.

### 5. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Crea todas las tablas (incluida `personal_access_tokens` de Sanctum) y deja un usuario demo (`cliente@demo.com` / `password123`) y 9 productos de ejemplo.

### 6. Generar la documentación Swagger

```bash
php artisan l5-swagger:generate
```

### 7. Levantar el servidor

```bash
php artisan serve
```

- API: `http://localhost:8000/api`
- Swagger UI: `http://localhost:8000/api/documentation`
