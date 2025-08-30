<?php

namespace App\Exceptions\Domain\Auth;

use Exception;

class InvalidTokenException extends Exception
{
    public function __construct()
    {
        parent::__construct('Token inválido ou expirado');
    }
}
