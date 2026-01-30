<?php

namespace toubilib\core\domain\exceptions;

use Exception;

/**
 * Exception levée lorsqu'un praticien est déjà occupé sur un créneau
 */
class PraticienOccupeException extends Exception
{
    public function __construct(string $message = "Praticien déjà occupé sur ce créneau")
    {
        parent::__construct($message);
    }
}
