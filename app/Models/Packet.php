<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public const STATUS_CREATED   = 'created';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED    = 'failed';

}
