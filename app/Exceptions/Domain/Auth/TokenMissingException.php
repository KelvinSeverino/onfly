<?php

namespace App\Exceptions\Domain\Auth;

use Exception;

class TokenMissingException extends Exception
{
    public function __construct()
    {
        parent::__construct('Token não encontrado');
    }
}
