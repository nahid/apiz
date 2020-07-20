<?php

namespace Apiz\Exceptions;

use Exception;
use Throwable;

class ClientNotDefinedException extends Exception
{
    public function __construct($message = "Client Not Defined", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
