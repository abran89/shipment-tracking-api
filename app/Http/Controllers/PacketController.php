<?php

namespace App\Http\Controllers;

use App\Enums\PacketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePacketRequest;
use App\Http\Requests\ShowPacketRequest;
use App\Http\Requests\UpdatePacketStatusRequest;
use App\Http\Requests\IndexPacketRequest;
use App\Http\Resources\PacketResource;
use App\Models\Packet;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\PacketService;


class PacketController extends Controller
{
    public function __construct(
        private readonly PacketService $packetService
    ) {}

    /**
     * Retorna la lista de envíos con filtro opcional por estado.
     */
    public function index(IndexPacketRequest $request): AnonymousResourceCollection
    {
        $status = $request->validated('status')
            ? PacketStatus::from($request->validated('status'))
            : null;

        return PacketResource::collection(
            $this->packetService->getAll($status)
        );
    }

    /**
     * Crea un nuevo envío con estado inicial created
     */
    public function store(CreatePacketRequest $request): JsonResponse
    {
        $packet = $this->packetService->create($request->validated());

        return (new PacketResource($packet))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Retorna el detalle de un envío por su ID
     */
    public function show(Packet $packet): PacketResource
    {
        return new PacketResource($packet);
    }

    /**
     * Actualiza el estado de un envío si la transición es válida.
     */
    public function updateStatus(UpdatePacketStatusRequest $request, Packet $packet): PacketResource
    {
        $newStatus = PacketStatus::from($request->validated('status'));
        $packet = $this->packetService->updateStatus($packet, $newStatus);

        return new PacketResource($packet);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
