<?php

namespace App\Services;

use App\Enums\PacketStatus;
use App\Models\Packet;
use Illuminate\Database\Eloquent\Collection;

class PacketService
{
    /**
     * Crea un nuevo envío con estado inicial created
     */
    public function create(array $data): Packet
    {
        return Packet::create([
            ...$data,
            'status' => PacketStatus::Created,
        ]);
    }

    /**
     * Retorna todos los envíos, con filtro opcional por estado
     */
    public function getAll(?PacketStatus $status): Collection
    {
        return Packet::when(
            $status,
            fn($query) => $query->where('status', $status->value)
        )->get();
    }

    /**
     * Retorna un envío por su ID
     */
    public function getById(int $id): Packet
    {
        return Packet::findOrFail($id);
    }

    /**
     * Actualiza el estado de un envío si la transición es válida
     */
    public function updateStatus(Packet $packet, PacketStatus $newStatus): Packet
    {
        if (!$packet->isValidTransition($newStatus)) {
            throw new InvalidStatusTransitionException($packet->status, $newStatus);
        }

        $packet->update(['status' => $newStatus]);

        return $packet;
    }
}