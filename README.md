# API de E-commerce Segura con Swagger — Laravel 12

API RESTful para la gestión de un e-commerce básico: registro y autenticación de clientes, catálogo de productos, procesamiento de órdenes de compra y pagos seguros mediante **Stripe**, con documentación completa en **Swagger/OpenAPI**.

> Proyecto evaluado — Tarea: "API de E-commerce Segura con Swagger Completo".

## 🧰 Stack técnico

- Laravel 12 / PHP 8.2+
- MySQL 8
- Laravel Sanctum (autenticación por tokens)
- Stripe (`stripe/stripe-php`)
- Swagger/OpenAPI (`darkaonline/l5-swagger`)

## 📁 Estructura relevante del proyecto


## 🚀 Instalación y configuración

### 1. Requisitos previos
- PHP >= 8.2 con extensiones: `mbstring`, `pdo_mysql`, `openssl`, `curl`, `json`
- Composer 2.x
- MySQL 8.x (XAMPP, Laragon, o instalación independiente)
- Cuenta de Stripe en modo test → https://dashboard.stripe.com/test/apikeys

### 2. Clonar el repositorio

```bash
git clone https://github.com/<tu-usuario>/<tu-repo>.git
cd <tu-repo>
```

### 3. Instalar dependencias

```bash
composer install
```

### 4. Configurar variables de entorno

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
DB_PASSWORD=tu_password

STRIPE_KEY=pk_test_xxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxx

L5_SWAGGER_CONST_HOST=http://localhost:8000
```

> ⚠️ **Importante sobre `DB_PORT`**: si usas XAMPP y tienes otro MySQL instalado en paralelo (por ejemplo, otro XAMPP, WAMP o un MySQL Server independiente), puede que tu instancia esté corriendo en un puerto distinto al 3306 por defecto (por ejemplo, **3307**). Verifica el puerto real en el panel de control de XAMPP (columna junto al servicio MySQL) y ajústalo en `DB_PORT`.

> ⚠️ **Importante sobre `STRIPE_KEY` / `STRIPE_SECRET`**: deben ser llaves **reales** de tu cuenta de Stripe en modo test (obténlas en https://dashboard.stripe.com/test/apikeys). Los valores de ejemplo `pk_test_xxxxxxxx` / `sk_test_xxxxxxxx` no funcionan y darán el error `Invalid API Key provided`. Pégalas sin comillas y sin espacios extra.

Consulta la tabla completa de variables documentadas al final de este README.

### 5. Crear la base de datos

**Opción A — phpMyAdmin (recomendada si usas XAMPP):**
1. Abre el panel de control de XAMPP → botón **Admin** junto a MySQL
2. En phpMyAdmin, ve a "Bases de datos" → crea `ecommerce_api` con collation `utf8mb4_unicode_ci`

**Opción B — línea de comandos:**
```bash
mysql -u root -e "CREATE DATABASE ecommerce_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```
> Si en Windows PowerShell te aparece `mysql: The term 'mysql' is not recognized...`, es porque el MySQL de XAMPP no está en el PATH del sistema. Usa la ruta completa en su lugar:
> ```powershell
> C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE ecommerce_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
> ```
> O simplemente usa la Opción A (phpMyAdmin).

### 6. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Esto crea las tablas base de Laravel (`users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`), la tabla de Sanctum (`personal_access_tokens`) y las tablas propias del e-commerce (`products`, `orders`, `order_items`, `payments`), y las puebla con:
- Un usuario demo: `cliente@demo.com` / `password123`
- 9 productos de ejemplo (8 activos, 1 inactivo para probar el filtro del catálogo público)

> Si en algún momento las migraciones quedan en un estado inconsistente (por ejemplo, si corriste migraciones parciales antes), usa `php artisan migrate:fresh --seed` para reiniciar la base de datos desde cero.

### 7. Generar la documentación Swagger

```bash
php artisan config:clear
php artisan l5-swagger:generate
```

> Si al generar te aparece un error como `Undefined array key "base"` (u otra clave), significa que la versión de `darkaonline/l5-swagger` instalada por Composer difiere ligeramente de la que se usó al escribir `config/l5-swagger.php`. La forma más segura de resolverlo de raíz es republicar el config oficial de tu versión instalada:
> ```bash
> rm config/l5-swagger.php
> php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
> ```
> Luego edita solo el campo `'title'` dentro de `'api' => [...]` con el nombre del proyecto, y vuelve a correr `php artisan l5-swagger:generate`.

### 8. Levantar el servidor

```bash
php artisan serve
```

- API base: `http://localhost:8000/api`
- **Documentación Swagger UI: `http://localhost:8000/api/documentation`**

### 9. (Opcional) Ejecutar los tests

```bash
php artisan test
```

## 🔐 Flujo de autenticación

1. `POST /api/auth/register` → crea el cliente y devuelve un `token` (Sanctum).
2. `POST /api/auth/login` → devuelve un `token` para clientes existentes.
3. Enviar el token en cada request protegido: `Authorization: Bearer {token}`.
4. En Swagger UI: clic en **Authorize** e ingresar `Bearer {token}`.

> ⚠️ **Cuidado al copiar el token**: copia **únicamente** el valor del campo `"token"` de la respuesta (algo como `1|aBcDeF123456...`), nunca el JSON completo de la respuesta. Un error común es pegar todo el objeto de respuesta en el campo de autorización, lo que produce un header inválido y respuestas 500/401 inesperadas.

## 🛒 Flujo completo de compra

1. `GET /api/products` — el cliente navega el catálogo (público, sin token).
2. `POST /api/auth/register` o `login` — el cliente se autentica.
3. `POST /api/orders` con `{"items":[{"product_id":1,"quantity":2}]}` — crea la orden (descuenta stock, calcula total).
4. `POST /api/orders/{id}/pay` — genera el `PaymentIntent` en Stripe y retorna el `client_secret`.
5. El frontend/app móvil usa Stripe.js o el SDK móvil de Stripe con ese `client_secret` para capturar los datos de tarjeta y confirmar el pago (esto ocurre del lado del cliente; el backend nunca recibe datos de tarjeta, cumpliendo PCI-DSS).
6. `GET /api/orders/{id}/payment/status` — sincroniza el estado real desde Stripe y actualiza la orden a `paid`/`failed`.
7. Stripe también puede notificar automáticamente vía `POST /api/stripe/webhook` (configura la URL en el Dashboard de Stripe → Webhooks, evento `payment_intent.succeeded`).

Tarjeta de prueba de Stripe: `4242 4242 4242 4242`, cualquier fecha futura y CVC.

Para confirmar un pago de prueba de punta a punta sin frontend, puedes usar [Stripe CLI](https://stripe.com/docs/stripe-cli):
```bash
stripe payment_intents confirm pi_XXXXXXXXXXXXX --payment-method=pm_card_visa
```

## 📋 Endpoints principales

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| POST | `/api/auth/register` | No | Registrar cliente |
| POST | `/api/auth/login` | No | Iniciar sesión |
| POST | `/api/auth/logout` | Sí | Cerrar sesión |
| GET | `/api/auth/me` | Sí | Perfil del cliente |
| GET | `/api/products` | No | Listado público de productos |
| GET | `/api/products/{id}` | No | Detalle de producto |
| POST | `/api/products` | Sí | Crear producto |
| PUT | `/api/products/{id}` | Sí | Editar producto |
| DELETE | `/api/products/{id}` | Sí | Eliminar producto |
| POST | `/api/orders` | Sí | Crear orden de compra |
| GET | `/api/orders` | Sí | Historial de compras |
| GET | `/api/orders/{id}` | Sí | Detalle de una orden |
| POST | `/api/orders/{id}/pay` | Sí | Iniciar pago (Stripe PaymentIntent) |
| GET | `/api/orders/{id}/payment/status` | Sí | Consultar estado del pago |
| POST | `/api/stripe/webhook` | No (firma Stripe) | Webhook de eventos de Stripe |

Documentación interactiva y detallada de cada endpoint (parámetros, cuerpos de petición, respuestas y códigos de error) disponible en **`/api/documentation`**.

## 🔧 Variables de entorno (`.env.example`)

| Variable | Descripción |
|---|---|
| `APP_KEY` | Clave de cifrado de la app (generada con `php artisan key:generate`) |
| `APP_URL` | URL base de la aplicación |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Credenciales de conexión a MySQL (revisa el puerto real de tu servicio) |
| `SANCTUM_STATEFUL_DOMAINS` | Dominios permitidos para autenticación stateful de Sanctum |
| `STRIPE_KEY` | Llave pública de Stripe (usada por el frontend) |
| `STRIPE_SECRET` | Llave secreta de Stripe (usada por el backend para crear PaymentIntents) — debe ser una llave real de tu cuenta test |
| `STRIPE_WEBHOOK_SECRET` | Secreto para validar la firma de los webhooks de Stripe |
| `L5_SWAGGER_CONST_HOST` | Host usado en la documentación Swagger generada |
| `L5_SWAGGER_GENERATE_ALWAYS` | Si es `true`, regenera la documentación en cada request (útil en desarrollo) |

## 🧱 Decisiones de arquitectura

- **Autenticación**: Laravel Sanctum (tokens personales), ideal para SPA/apps móviles consumiendo la API.
- **Validación**: Form Requests dedicados por acción, con mensajes en español y respuesta JSON 422 consistente.
- **Errores**: manejados centralmente en `bootstrap/app.php` (patrón de Laravel 12) — toda ruta `/api/*` responde siempre `{"success": false, "message": ..., "errors"?: ...}`.
- **Transacciones y concurrencia**: la creación de órdenes usa `DB::transaction` + `lockForUpdate()` sobre productos para evitar sobreventa de stock ante compras simultáneas.
- **Separación de responsabilidades**: la lógica de Stripe vive en `app/Services/StripeService.php`, no en el controlador.
- **Seguridad de pagos**: el backend nunca recibe ni almacena datos de tarjeta; solo crea `PaymentIntents` y confía en Stripe.js/SDK del cliente para la captura (cumplimiento PCI-DSS).
- **Soft deletes** en productos para no perder el historial de órdenes que los referencian.

## 🧪 Pruebas rápidas con cURL

```bash
# Registro
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Juan Pérez","email":"juan@example.com","password":"secreto123","password_confirmation":"secreto123"}'

# Listar productos
curl http://localhost:8000/api/products

# Crear orden (usa el token recibido en el registro/login, solo el valor de "token")
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"product_id":1,"quantity":2}]}'
```

## 🔧 Troubleshooting

- **`Table 'ecommerce_api.users' doesn't exist`**: las migraciones deben incluir las tablas base de Laravel además de las propias del e-commerce. Corre `php artisan migrate:fresh --seed` para reconstruir todo desde cero en el orden correcto.
- **`Table 'ecommerce_api.personal_access_tokens' doesn't exist`**: falta la migración de Sanctum. Confirma que exista un archivo de migración para `personal_access_tokens` en `database/migrations/` y vuelve a migrar.
- **`Undefined array key "base"` (o similar) al generar Swagger**: desajuste de versión entre el `config/l5-swagger.php` del repo y el paquete instalado. Republica el config oficial: `php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"`.
- **Swagger no muestra los endpoints**: ejecuta `php artisan l5-swagger:generate` de nuevo tras cualquier cambio en las anotaciones `@OA\...`, y confirma `L5_SWAGGER_GENERATE_ALWAYS=true` en `.env` durante desarrollo.
- **Error 401/500 en rutas protegidas**: revisa que el header `Authorization` sea exactamente `Bearer {token}` (solo el valor del campo `token`, sin JSON completo ni palabra "Bearer" duplicada), y que el token no haya sido revocado (logout).
- **`Invalid API Key provided` de Stripe**: confirma que `STRIPE_SECRET` en `.env` sea una llave real (no el valor de ejemplo), sin comillas ni espacios extra, y corre `php artisan config:clear` después de cambiarla.
- **`mysql` no reconocido en PowerShell**: el MySQL de XAMPP no está en el PATH. Usa la ruta completa `C:\xampp\mysql\bin\mysql.exe` o administra la base de datos desde phpMyAdmin.

## 📄 Licencia

MIT