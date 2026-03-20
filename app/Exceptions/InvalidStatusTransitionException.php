<?php

namespace App\Exceptions;

use Exception;
use App\Enums\PacketStatus;

class InvalidStatusTransitionException extends \RuntimeException
{
    public function __construct(PacketStatus $from, PacketStatus $to)
    {
        parent::__construct(
            "Transición inválida de {$from->value} a {$to->value}."
        );
    }
}