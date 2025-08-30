<?php

namespace App\Exceptions\Domain\Auth;

use Exception;

class UserNotAuthenticatedException  extends Exception
{
    public function __construct()
    {
        parent::__construct('Usuário não autenticado');
    }
}
