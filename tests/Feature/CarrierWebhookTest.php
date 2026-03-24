<?php

use App\Enums\PacketStatus;
use App\Models\Packet;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function validPayload(array $override = []): array
{
    $body = array_merge([
        'tracking_code' => 'ABC-123',
        'status'        => 'delivered',
        'timestamp'     => '2026-03-24T09:30:00Z',
    ], $override);

    $signature = 'sha256=' . hash_hmac('sha256', json_encode($body), config('services.carrier_webhook_secret'));

    return array_merge($body, ['signature' => $signature]);
}

it('actualiza el estado a delivered con firma válida', function () {
    Packet::factory()->create([
        'tracking_code' => 'ABC-123',
        'status'        => PacketStatus::InTransit,
    ]);

    $response = $this->postJson('/api/webhooks/carrier', validPayload());

    $response->assertOk()
             ->assertJsonFragment(['message' => 'Estado actualizado correctamente.']);

    $this->assertDatabaseHas('packets', [
        'tracking_code' => 'ABC-123',
        'status'        => PacketStatus::Delivered->value,
    ]);
});

it('retorna 401 con firma inválida', function () {
    Packet::factory()->create(['tracking_code' => 'ABC-123']);

    $response = $this->postJson('/api/webhooks/carrier', [
        'tracking_code' => 'ABC-123',
        'status'        => 'delivered',
        'timestamp'     => '2026-03-24T09:30:00Z',
        'signature'     => 'sha256=firma-invalida',
    ]);

    $response->assertUnauthorized()
             ->assertJsonFragment(['message' => 'Firma inválida.']);
});

it('retorna 404 si el tracking code no existe', function () {
    $response = $this->postJson('/api/webhooks/carrier', validPayload([
        'tracking_code' => 'NO-EXISTE',
    ]));

    $response->assertNotFound()
             ->assertJsonFragment(['message' => 'No query results for model [App\\Models\\Packet].']);
});

it('retorna 422 con status inválido', function () {
    $response = $this->postJson('/api/webhooks/carrier', validPayload([
        'status' => 'in_transit',
    ]));

    $response->assertUnprocessable()
             ->assertJsonValidationErrors(['status']);
});

it('retorna 422 con campos faltantes', function () {
    $response = $this->postJson('/api/webhooks/carrier', []);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'tracking_code',
                 'status',
                 'timestamp',
                 'signature',
             ]);
});