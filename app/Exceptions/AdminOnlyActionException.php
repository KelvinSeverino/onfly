<?php

namespace App\Exceptions;

use Exception;

class AdminOnlyActionException extends Exception
{
    public function __construct($message = 'Recurso somente para administradores.')
    {
        parent::__construct($message, 403);
    }
}
