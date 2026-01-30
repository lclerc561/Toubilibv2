<?php

namespace toubilib\core\domain\exceptions;

use Exception;

/**
 * Exception levée lorsqu'un créneau horaire est invalide
 */
class CreneauInvalideException extends Exception
{
    public function __construct(string $message = "Créneau horaire invalide")
    {
        parent::__construct($message);
    }
}
