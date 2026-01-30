<?php

namespace toubilib\core\domain\exceptions;

use Exception;

/**
 * Exception levée lorsqu'un motif de visite n'est pas autorisé
 */
class MotifVisiteInvalideException extends Exception
{
    public function __construct(string $message = "Motif de visite non autorisé pour ce praticien")
    {
        parent::__construct($message);
    }
}
