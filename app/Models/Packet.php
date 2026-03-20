<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PacketStatus;

class Packet extends Model
{
     protected $fillable = [
        'tracking_code',
        'recipient_name',
        'recipient_email',
        'destination_address',
        'weight_grams',
        'status',
    ];

    /**
    * Castea el campo status al enum PacketStatus automáticamente.
    */
    protected $casts = [
        'status' => PacketStatus::class,
    ];

    /**
     * Determina si el cambio a un nuevo estado es válido.
     */
    public function isValidTransition(PacketStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

}
