<?php

namespace App\Enums;

enum PacketStatus: string
{
    case Created   = 'created';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Failed    = 'failed';

    /**
     * Retorna los estados a los que se puede transitar desde el estado actual.
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::Created   => [self::InTransit],
            self::InTransit => [self::Delivered, self::Failed],
            self::Delivered => [],
            self::Failed    => [],
        };
    }

    /**
     * Determina si la transición hacia el nuevo estado es válida.
     */
    public function canTransitionTo(PacketStatus $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }
}
