<?php

use App\Enums\PacketStatus;
use App\Models\Packet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// POST /api/packets

it('crea un envío con los datos correctos', function () {
    $response = $this->postJson('/api/packets', [
        'tracking_code'       => 'ABC-123',
        'recipient_name'      => 'Juan Pérez',
        'recipient_email'     => 'juan@example.com',
        'destination_address' => 'Av. Siempre Viva 123',
        'weight_grams'        => 500,
    ]);

    $response->assertCreated()
             ->assertJsonFragment([
                 'tracking_code' => 'ABC-123',
                 'status'        => PacketStatus::Created->value,
             ]);

    $this->assertDatabaseHas('packets', ['tracking_code' => 'ABC-123']);
});

it('no crea un envío con tracking_code duplicado', function () {
    Packet::factory()->create(['tracking_code' => 'ABC-123']);

    $response = $this->postJson('/api/packets', [
        'tracking_code'       => 'ABC-123',
        'recipient_name'      => 'Juan Pérez',
        'recipient_email'     => 'juan@example.com',
        'destination_address' => 'Av. Siempre Viva 123',
        'weight_grams'        => 500,
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors(['tracking_code']);
});

it('no crea un envío con campos requeridos faltantes', function () {
    $response = $this->postJson('/api/packets', []);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'tracking_code',
                 'recipient_name',
                 'recipient_email',
                 'destination_address',
                 'weight_grams',
             ]);
});

it('no crea un envío con email inválido', function () {
    $response = $this->postJson('/api/packets', [
        'tracking_code'       => 'ABC-123',
        'recipient_name'      => 'Juan Pérez',
        'recipient_email'     => 'no-es-un-email',
        'destination_address' => 'Av. Siempre Viva 123',
        'weight_grams'        => 500,
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors(['recipient_email']);
});

it('no crea un envío con weight_grams menor a 1', function () {
    $response = $this->postJson('/api/packets', [
        'tracking_code'       => 'ABC-123',
        'recipient_name'      => 'Juan Pérez',
        'recipient_email'     => 'juan@example.com',
        'destination_address' => 'Av. Siempre Viva 123',
        'weight_grams'        => 0,
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors(['weight_grams']);
});


// GET /api/packets

it('retorna la lista de envíos', function () {
    Packet::factory()->count(3)->create();

    $response = $this->getJson('/api/packets');

    $response->assertOk()
             ->assertJsonCount(3, 'data');
});

it('filtra los envíos por estado', function () {
    Packet::factory()->count(2)->create(['status' => PacketStatus::Created]);
    Packet::factory()->count(3)->create(['status' => PacketStatus::InTransit]);

    $response = $this->getJson('/api/packets?status=in_transit');

    $response->assertOk()
             ->assertJsonCount(3, 'data');
});

it('retorna lista vacía cuando no hay envíos', function () {
    $response = $this->getJson('/api/packets');

    $response->assertOk()
             ->assertJsonCount(0, 'data');
});


// GET /api/packets/{id}

it('retorna el detalle de un envío', function () {
    $packet = Packet::factory()->create();

    $response = $this->getJson("/api/packets/{$packet->id}");

    $response->assertOk()
             ->assertJsonFragment(['tracking_code' => $packet->tracking_code]);
});

it('retorna 404 si el envío no existe', function () {
    $response = $this->getJson('/api/packets/999');

    $response->assertNotFound();
});


// PUT /api/packets/{id}/status

it('actualiza el estado de un envío con una transición válida', function () {
    $packet = Packet::factory()->create(['status' => PacketStatus::Created]);

    $response = $this->putJson("/api/packets/{$packet->id}/status", [
        'status' => PacketStatus::InTransit->value,
    ]);

    $response->assertOk()
             ->assertJsonFragment(['status' => PacketStatus::InTransit->value]);

    $this->assertDatabaseHas('packets', [
        'id'     => $packet->id,
        'status' => PacketStatus::InTransit->value,
    ]);
});

it('retorna error al intentar una transición inválida', function () {
    $packet = Packet::factory()->create(['status' => PacketStatus::Created]);

    $response = $this->putJson("/api/packets/{$packet->id}/status", [
        'status' => PacketStatus::Delivered->value,
    ]);

    $response->assertUnprocessable()
             ->assertJsonFragment(['message' => "Transición inválida de created a delivered."]);
});

it('retorna 404 al actualizar el estado de un envío inexistente', function () {
    $response = $this->putJson('/api/packets/999/status', [
        'status' => PacketStatus::InTransit->value,
    ]);

    $response->assertNotFound();
});

it('retorna error con un estado inválido', function () {
    $packet = Packet::factory()->create();

    $response = $this->putJson("/api/packets/{$packet->id}/status", [
        'status' => 'estado_invalido',
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors(['status']);
});