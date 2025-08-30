<?php

namespace App\Exceptions\Domain\TravelRequest;

use Exception;

class TravelRequestActionNotAllowedException extends Exception
{
    public function __construct($message = 'Não autorizado gerenciar este pedido.', $httpCode = 403)
    {
        parent::__construct($message, $httpCode);
    }
}
