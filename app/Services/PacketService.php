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
    private const CACHE_TTL_MINUTES = 5;
    /**
     * Crea un nuevo envío con estado inicial created
     */
    public function create(array $data): Packet
    {
        $packet = Packet::create([
            ...$data,
            'status' => PacketStatus::Created,
        ]);

        $this->flushCache(PacketStatus::Created);

        return $packet;
    }

    /**
     * Retorna todos los envíos, con filtro opcional por estado
     * La respuesta se cachea por 5 minutos
     */
    public function getAll(?PacketStatus $status = null): Collection
    {
        $cacheKey = 'packets.all' . ($status ? ".{$status->value}" : '');

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => Packet::when($status, fn ($q) => $q->where('status', $status->value))->get()
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
        $packet->refresh();

        $this->flushCache($oldStatus, $newStatus);

        return $packet;
    }

     /**
     * Invalida el cache general y el de cada estado afectado.
     */
    private function flushCache(PacketStatus ...$statuses): void
    {
        Cache::forget('packets.all');

        foreach ($statuses as $status) {
            Cache::forget("packets.all.{$status->value}");
        }
    }
}