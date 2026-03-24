# Shipment Tracking API

API en Laravel 12 para gestionar el seguimiento de paquetes.

## Instalación

```bash
git clone https://github.com/abran89/shipment-tracking-api.git
cd shipment-tracking-api
composer install
cp .env.example .env        # Linux / macOS
copy .env.example .env      # Windows
php artisan key:generate
php artisan migrate
php artisan serve
```

## Configuración

El proyecto usa SQLite por defecto. Las variables principales están en `.env`:

- `APP_NAME`, `APP_ENV`, `APP_DEBUG`
- `DB_CONNECTION=sqlite`
- `LOG_LEVEL=warning`, `LOG_CHANNEL=single`
- `QUEUE_CONNECTION=database`
- `WEBHOOK_URL=` - URL para notificaciones de cambio de estado
- `CARRIER_WEBHOOK_SECRET=` - Secret para validar firma del webhook

## Tecnologías

- Laravel 12
- PHP 8.2+
- SQLite
- Pest (tests)

## Arquitectura

- **Service Layer**: [`PacketService.php`](app/Services/PacketService.php) maneja la lógica de negocio
- **Form Requests**: Validaciones en [`app/Http/Requests/`](app/Http/Requests/)
- **API Resources**: Respuestas transformadas en [`PacketResource.php`](app/Http/Resources/PacketResource.php)
- **Estados**: Enum [`PacketStatus.php`](app/Enums/PacketStatus.php) con transiciones controladas

## Decisiones de Diseño

### Caché

El listado de envíos se cachea durante 5 minutos para mejorar rendimiento:
- Clave `packets.all` - lista sin filtro
- Clave `packets.all.{status}` - lista filtrada por estado

Al crear o actualizar un envío, se invalida el caché general y el de los estados afectados.

### Validaciones

- **Firma del webhook**: HMAC SHA256 para validar autenticidad del transportista
- **Transiciones de estado**: Solo se permiten transiciones válidas (created → in_transit → delivered/failed)
- **Códigos de seguimiento**: Únicos por envío

### Variables de Entorno en Producción

En entorno de **producción**, las siguientes variables son obligatorias y se validan en [`AppServiceProvider.php`](app/Providers/AppServiceProvider.php):

- `CARRIER_WEBHOOK_SECRET` - Secret para validar la firma del webhook del transportista
- `WEBHOOK_URL` - URL donde se enviarán las notificaciones de cambio de estado

Si alguna no está configurada, la aplicación no arrancará en producción.

## Endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/packets` | Crear envío |
| GET | `/api/packets` | Listar envíos (filtro por `?status=`) |
| GET | `/api/packets/{id}` | Ver detalle |
| PUT | `/api/packets/{id}/status` | Cambiar estado |
| POST | `/api/webhooks/carrier` | Webhook del transportista (actualiza a delivered) |

Estados: `created` → `in_transit` → `delivered` o `failed`

## Probar con Postman

Importar [`postman/shipment-tracking-api.postman_collection.json`](postman/shipment-tracking-api.postman_collection.json) y configurar la variable `base_url`.

## Comando para generar firma de webhook

Para probar el endpoint `/api/webhooks/carrier`, puedes usar el comando:

```bash
php artisan carrier:signature ABC-123 delivered "2026-03-24T09:30:00Z"
```

Este comando genera el payload completo con la firma HMAC SHA256.

## Tests

```bash
php artisan test
```

### Pruebas Unitarias (`tests/Unit/`)

- `PacketServiceTest.php` (7 tests):
  - Creación de envíos invalida caché
  - Listado sin filtro se guarda en cache
  - Listado con filtro se guarda en cache con clave del estado
  - Segunda llamada usa cache (sin query a BD)
  - Sucesivas llamadas retornan datos desde cache
  - Actualización de estado invalida cache general y de estados
  - Después de invalidar cache se hace query a la base de datos

- `PacketStatusTest.php` (8 tests):
  - Transiciones válidas: created → in_transit → delivered/failed
  - Transiciones inválidas bloqueadas
  - Estados terminales sin transiciones

### Pruebas Feature (`tests/Feature/`)

- `PacketControllerTest.php` (14 tests):
  - Creación con validaciones (tracking duplicado, email, peso)
  - Listado con y sin filtros
  - Detalle y error 404
  - Actualización de estado con transiciones válidas/inválidas

- `CarrierWebhookTest.php` (5 tests):
  - Actualiza estado con firma válida
  - Retorna 401 con firma inválida
  - Retorna 404 si el tracking no existe
  - Validaciones de status y campos

## Queue Worker

El job `SendStatusWebhook` se ejecuta de forma asíncrona cuando cambia el estado de un paquete. Configuración:

```env
QUEUE_CONNECTION=database
```

### Ejecutar el worker

```bash
php artisan queue:work 
```

### Reintentos

El job tiene configurado `$tries = 2` con backoff de 10 segundos. Si todos los intentos fallan, se registra en `failed_jobs`.

## Estructura

```
app/
├── Enums/PacketStatus.php
├── Exceptions/InvalidStatusTransitionException.php
├── Http/Controllers/PacketController.php
├── Http/Middleware/ForceJsonAccept.php
├── Http/Requests/
├── Http/Resources/PacketResource.php
├── Models/Packet.php
└── Services/PacketService.php
