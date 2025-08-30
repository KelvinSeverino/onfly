<?php

namespace App\Exceptions\Domain\Auth;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Credenciais inválidas');
    }
}