<?php

namespace App\Exceptions;

use Exception;

class InvalidSignatureException extends Exception
{
    public function __construct()
    {
        parent::__construct('Firma inválida.');
    }
}
