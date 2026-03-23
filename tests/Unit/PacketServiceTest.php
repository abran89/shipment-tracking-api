<?php

use App\Enums\PacketStatus;
use App\Models\Packet;
use App\Services\PacketService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(PacketService::class);
});

/**
 * Verifica que al crear un nuevo envío se invalide el cache general
 * y el cache específico del estado inicial (created).
 */
it('al crear un envío se invalida el cache', function () {
    Cache::spy();

    $this->service->create([
        'tracking_code'       => 'ABC-123',
        'recipient_name'     => 'Juan Pérez',
        'recipient_email'    => 'juan@example.com',
        'destination_address' => 'Av. Siempre Viva 123',
        'weight_grams'       => 500,
    ]);

    Cache::shouldHaveReceived('forget')
        ->with('packets.all')
        ->once();
    Cache::shouldHaveReceived('forget')
        ->with('packets.all.created')
        ->once();
});

/**
 * Verifica que al obtener todos los envíos sin filtro se guarde
 * el resultado en cache con la clave 'packets.all'.
 */
it('al obtener envíos sin filtro se guarda en cache', function () {
    Cache::spy();

    $this->service->getAll();

    Cache::shouldHaveReceived('remember')
        ->with('packets.all', Mockery::any(), Mockery::any())
        ->once();
});

/**
 * Verifica que al obtener envíos con filtro por estado se guarde
 * en cache con una clave específica para ese estado.
 */
it('al obtener envíos con filtro se guarda en cache con clave del estado', function () {
    Cache::spy();

    $this->service->getAll(PacketStatus::Created);

    Cache::shouldHaveReceived('remember')
        ->with('packets.all.created', Mockery::any(), Mockery::any())
        ->once();
});

/**
 * Verifica que la segunda llamada a getAll() use el cache
 * y no realice una nueva consulta a la base de datos.
 */
it('la segunda llamada a getAll usa cache y no hace query', function () {
    Packet::factory()->count(3)->create();

    $this->service->getAll();

    $queryCount = 0;
    DB::listen(fn () => $queryCount++);

    $this->service->getAll();

    expect($queryCount)->toBe(0);
});

/**
 * Verifica que los datos retornados en la segunda llamada
 * sean idénticos a los de la primera (vienen del cache).
 */
it('las sucesivas llamadas retornan los mismos datos desde cache', function () {
    Packet::factory()->count(3)->create();

    $primera = $this->service->getAll();
    $segunda = $this->service->getAll();

    expect($segunda->pluck('id'))->toEqual($primera->pluck('id'));
});

/**
 * Verifica que al actualizar el estado de un envío se invalide
 * el cache general y los caches de los estados afectados.
 */
it('al actualizar estado se invalida cache general y de estados', function () {
    $packet = Packet::factory()->create(['status' => PacketStatus::Created]);

    Cache::spy();

    $this->service->updateStatus($packet, PacketStatus::InTransit);

    Cache::shouldHaveReceived('forget')
        ->with('packets.all')
        ->once();
    Cache::shouldHaveReceived('forget')
        ->with('packets.all.created')
        ->once();
    Cache::shouldHaveReceived('forget')
        ->with('packets.all.in_transit')
        ->once();
});

/**
 * Verifica que después de invalidar el cache, la siguiente llamada
 * a getAll() realice una nueva consulta a la base de datos.
 */
it('después de invalidar cache se hace query a la base de datos', function () {
    $packet = Packet::factory()->create(['status' => PacketStatus::Created]);

    $this->service->getAll();

    $this->service->updateStatus($packet, PacketStatus::InTransit);

    Cache::flush();
    $result = $this->service->getAll();

    expect($result)->toHaveCount(1);
});