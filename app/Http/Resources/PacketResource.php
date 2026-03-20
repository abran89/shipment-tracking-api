<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PacketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'tracking_code'        => $this->tracking_code,
            'recipient_name'       => $this->recipient_name,
            'recipient_email'      => $this->recipient_email,
            'destination_address'  => $this->destination_address,
            'weight_grams'         => $this->weight_grams,
            'status'               => $this->status->value,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
