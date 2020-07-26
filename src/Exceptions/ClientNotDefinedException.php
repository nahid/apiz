<?php

namespace Apiz\Exceptions;

use Exception;

class ClientNotDefinedException extends Exception
{
    public function __construct($message = "Client Not Defined", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
