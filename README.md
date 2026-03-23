# Shipment Tracking API

API en Laravel 12 para gestionar el seguimiento de paquetes.

## Instalación

```bash
composer install
cp .env.example .env
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

## Endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/packets` | Crear envío |
| GET | `/api/packets` | Listar envíos (filtro por `?status=`) |
| GET | `/api/packets/{id}` | Ver detalle |
| PUT | `/api/packets/{id}/status` | Cambiar estado |

Estados: `created` → `in_transit` → `delivered` o `failed`

## Probar con Postman

Importar [`postman/shipment-tracking-api.postman_collection.json`](postman/shipment-tracking-api.postman_collection.json) y configurar la variable `base_url`.

## Tests

```bash
php artisan test
```

## Queue Worker

El job `SendStatusWebhook` se ejecuta de forma asíncrona cuando cambia el estado de un paquete. Configuración:

```env
QUEUE_CONNECTION=database
```

### Ejecutar el worker

```bash
php artisan queue:work database --once  # procesar un job
php artisan queue:work database         # ejecutar continuamente
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
