# Shipment Tracking API

API REST para gestión de envíospaquetes con tracking.

## Requisitos

- PHP 8.2+
- Composer
- Laravel 12

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Endpoints API

### POST /api/packets
Crear un nuevo envío

**Request:**
```json
{
    "tracking_code": "ABC-123",
    "recipient_name": "Juan Pérez",
    "recipient_email": "juan@example.com",
    "destination_address": "Av. Siempre Viva 123",
    "weight_grams": 500
}
```

**Respuesta (201):**
```json
{
    "data": {
        "id": 1,
        "tracking_code": "ABC-123",
        "recipient_name": "Juan Pérez",
        "recipient_email": "juan@example.com",
        "destination_address": "Av. Siempre Viva 123",
        "weight_grams": 500,
        "status": "created"
    }
}
```

---

### GET /api/packets
Listar todos los envíos (con paginación)

**Query Parameters:**
- `status` (optional) - Filtrar por estado: `created`, `in_transit`, `delivered`, `failed`

**Respuesta (200):**
```json
{
    "data": [
        {
            "id": 1,
            "tracking_code": "ABC-123",
            "status": "created"
        }
    ]
}
```

---

### GET /api/packets/{id}
Ver detalle de un envío específico

**Respuesta (200):**
```json
{
    "data": {
        "id": 1,
        "tracking_code": "ABC-123",
        "recipient_name": "Juan Pérez",
        "recipient_email": "juan@example.com",
        "destination_address": "Av. Siempre Viva 123",
        "weight_grams": 500,
        "status": "created"
    }
}
```

---

### PUT /api/packets/{id}/status
Actualizar el estado de un envío

**Request:**
```json
{
    "status": "in_transit"
}
```

**Transiciones válidas:**
- `created` → `in_transit`
- `in_transit` → `delivered`
- `in_transit` → `failed`

**Respuesta (200):**
```json
{
    "data": {
        "id": 1,
        "tracking_code": "ABC-123",
        "status": "in_transit"
    }
}
```

**Respuesta error (422) - Transición inválida:**
```json
{
    "message": "Transición inválida de created a delivered."
}
```

---

## Probar con Postman

1. Importar colección desde `postman/Shipment API.postman_collection.json`
2. Ejecutar `php artisan serve`
3. URL base: `http://127.0.0.1:8000`

## Tests

```bash
php artisan test
```

### Pruebas disponibles:
- **Unitarias:** [`tests/Unit/PacketStatusTest.php`](tests/Unit/PacketStatusTest.php) - Lógica de transiciones de estado
- **Feature:** [`tests/Feature/PacketControllerTest.php`](tests/Feature/PacketControllerTest.php) - Endpoints de la API
