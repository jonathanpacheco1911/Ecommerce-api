# Ecommerce API

API REST para un e-commerce básico construida con Laravel 12. Maneja registro/login de clientes, catálogo de productos, órdenes de compra y pagos con Stripe. Documentada con Swagger (OpenAPI).

## Stack

- Laravel 12, PHP 8.2+
- MySQL
- Sanctum para auth por token
- Stripe (`stripe/stripe-php`)
- `darkaonline/l5-swagger` para la documentación

## Requisitos

- PHP 8.2+ con `pdo_mysql`, `mbstring`, `curl`, `openssl` habilitadas
- Composer
- MySQL 8 (sirve XAMPP, Laragon, o una instalación normal)
- Cuenta de Stripe en modo test (gratis, no pide tarjeta): https://dashboard.stripe.com/register

## Setup

```bash
git clone https://github.com/jonathanpacheco1911/Ecommerce-api.git
cd Ecommerce-api
composer install
cp .env.example .env
php artisan key:generate
```

Edita `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306          # revisa tu puerto real, con XAMPP a veces es 3307
DB_DATABASE=ecommerce_api
DB_USERNAME=root
DB_PASSWORD=

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

Las llaves de Stripe las sacas de https://dashboard.stripe.com/test/apikeys. Los placeholders del `.env.example` no funcionan, tienen que ser llaves reales de tu cuenta test.

Crea la base de datos (por phpMyAdmin o por terminal):

```bash
mysql -u root -e "CREATE DATABASE ecommerce_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Si estás en Windows con XAMPP y `mysql` no se reconoce como comando, usa la ruta completa (`C:\xampp\mysql\bin\mysql.exe`) o simplemente crea la base desde phpMyAdmin.

Migra y siembra datos:

```bash
php artisan migrate --seed
```

Esto crea todas las tablas (incluidas las de Sanctum) y deja un usuario demo (`cliente@demo.com` / `password123`) más 9 productos de ejemplo.

Genera la doc de Swagger y levanta el server:

```bash
php artisan l5-swagger:generate
php artisan serve
```

- API: `http://localhost:8000/api`
- Swagger UI: `http://localhost:8000/api/documentation`

## Probar el flujo completo

1. `POST /api/auth/register` — te devuelve un `token`. Copia solo ese valor (no el JSON completo).
2. En Swagger, botón **Authorize**, pega `Bearer {token}`.
3. `GET /api/products` — catálogo público, no necesita token.
4. `POST /api/orders` con `{"items":[{"product_id":1,"quantity":2}]}` — crea la orden y descuenta stock.
5. `POST /api/orders/{id}/pay` — genera el PaymentIntent en Stripe, devuelve `client_secret`.
6. Confirmar el pago es responsabilidad del frontend (Stripe.js), este backend nunca toca datos de tarjeta. Para probar sin frontend, usa Stripe CLI:
```bash
   stripe payment_intents confirm pi_XXXX --payment-method=pm_card_visa
```
7. `GET /api/orders/{id}/payment/status` — sincroniza el estado real desde Stripe.

Tarjeta de prueba: `4242 4242 4242 4242`, cualquier fecha futura, cualquier CVC.

## Endpoints

| Método | Ruta | Auth |
|---|---|---|
| POST | `/api/auth/register` | No |
| POST | `/api/auth/login` | No |
| POST | `/api/auth/logout` | Sí |
| GET | `/api/auth/me` | Sí |
| GET | `/api/products` | No |
| GET | `/api/products/{id}` | No |
| POST/PUT/DELETE | `/api/products/{id}` | Sí |
| POST | `/api/orders` | Sí |
| GET | `/api/orders` | Sí |
| GET | `/api/orders/{id}` | Sí |
| POST | `/api/orders/{id}/pay` | Sí |
| GET | `/api/orders/{id}/payment/status` | Sí |
| POST | `/api/stripe/webhook` | No (validado por firma) |

Detalle completo de cada uno (bodies, respuestas, códigos de error) en `/api/documentation`.

## Decisiones de diseño

- **Sanctum en vez de Passport**: no necesitamos OAuth2 completo, solo tokens simples para consumo API/móvil.
- **Lock pesimista al crear órdenes** (`lockForUpdate()` dentro de una transacción): evita que dos compras simultáneas sobrevendan el mismo stock.
- **Stripe aislado en un Service** (`app/Services/StripeService.php`), no en el controlador — más fácil de testear y de mockear si algún día se agrega otra pasarela.
- **Idempotency key en el PaymentIntent** (`order-{id}-payment-intent`): si el cliente reintenta el pago por un timeout de red, no se duplica el cargo.
- **Webhook separado por tipo de evento** (`succeeded`, `payment_failed`, `canceled`), no solo por el status genérico del objeto — más explícito y más fácil de extender.
- El backend nunca recibe datos de tarjeta, solo crea/consulta PaymentIntents. La captura la hace el cliente con Stripe.js/SDK (PCI-DSS).

## Troubleshooting (cosas que realmente me pasaron armando esto)

**`Table 'users' doesn't exist` al migrar** — si ves esto, revisa que las migraciones base de Laravel (`0001_01_01_...`) estén presentes junto a las del e-commerce. Si el orden se rompió por algún motivo, `php artisan migrate:fresh --seed` reconstruye todo limpio.

**`Table 'personal_access_tokens' doesn't exist`** — falta la migración de Sanctum. Sin ella, cualquier ruta protegida con `auth:sanctum` tira 500 en vez de 401.

**Error al generar Swagger (`Undefined array key ...`)** — pasa cuando la versión de `l5-swagger` instalada no calza con el config. Si vuelve a pasar, la solución más rápida es republicar el config oficial:
```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

**`Invalid API Key provided` de Stripe** — casi siempre es porque quedó el placeholder del `.env.example` en vez de la llave real, o porque no corriste `php artisan config:clear` después de cambiar el `.env`.

**401/500 raros al llamar endpoints protegidos** — revisa el header `Authorization`. Debe ser `Bearer {token}` exacto, solo el valor del campo `token` de la respuesta de login/register, no el JSON completo.

**`mysql` no reconocido en PowerShell** — el MySQL de XAMPP no está en el PATH. O usas la ruta completa (`C:\xampp\mysql\bin\mysql.exe`) o administras la BD desde phpMyAdmin.

## Tests

```bash
php artisan test
```

Cubre: listado público de productos, bloqueo de creación sin auth, creación de orden con descuento de stock, y rechazo por stock insuficiente.
