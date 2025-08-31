<?php

namespace App\Exceptions\Domain\TravelRequest;

use Exception;

class TravelRequestActionNotAllowedException extends Exception
{
    public function __construct(string $message = 'Não autorizado gerenciar este pedido.', ?int $httpCode = 403)
    {
        parent::__construct($message, $httpCode);
    }
}
