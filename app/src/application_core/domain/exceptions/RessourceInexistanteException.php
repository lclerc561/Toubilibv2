<?php

namespace toubilib\core\domain\exceptions;

use Exception;

/**
 * Exception levée lorsqu'une ressource demandée n'existe pas
 */
class RessourceInexistanteException extends Exception
{
    public function __construct(string $ressource, string $id = '')
    {
        $message = $id ? "{$ressource} avec l'ID {$id} inexistant" : "{$ressource} inexistant";
        parent::__construct($message);
    }
}
