<?php

namespace toubilib\core\domain\exceptions;

use Exception;

/**
 * Exception levée lorsqu'un praticien est indisponible sur un créneau
 */
class PraticienIndisponibleException extends Exception
{
    public function __construct(string $message = "Praticien indisponible sur ce créneau")
    {
        parent::__construct($message);
    }
}
