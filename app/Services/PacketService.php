<?php

namespace App\Services;

use App\Enums\PacketStatus;
use App\Models\Packet;
use App\Exceptions\PacketNotFoundException;
use App\Exceptions\InvalidStatusTransitionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PacketService
{
    /**
     * Crea un nuevo envío con estado inicial created
     */
    public function create(array $data): Packet
    {
       $packet = Packet::create([
            ...$data,
            'status' => PacketStatus::Created,
        ]);

        Cache::forget('packets.all');
        Cache::forget('packets.all.created');

        return $packet->fresh();
    }

    /**
     * Retorna todos los envíos, con filtro opcional por estado
     * La respuesta se cachea por 5 minutos
     */
    public function getAll(?PacketStatus $status = null): Collection
    {
        $cacheKey = 'packets.all' . ($status ? ".{$status->value}" : '');

        return Cache::remember($cacheKey, now()->addMinutes(5), fn() =>
            Packet::when(
                $status,
                fn($query) => $query->where('status', $status->value)
            )->get()
        );
    }

    /**
     * Actualiza el estado de un envío si la transición es válida
     */
    public function updateStatus(Packet $packet, PacketStatus $newStatus): Packet
    {
        if (!$packet->isValidTransition($newStatus)) {
            throw new InvalidStatusTransitionException($packet->status, $newStatus);
        }

        $oldStatus = $packet->status;
        $packet->update(['status' => $newStatus]);

        Cache::forget('packets.all');
        Cache::forget("packets.all.{$oldStatus->value}");
        Cache::forget("packets.all.{$newStatus->value}");

        return $packet->fresh();
    }
}