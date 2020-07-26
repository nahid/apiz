<?php

namespace Apiz\Exceptions;

use Exception;

class NoResponseException extends Exception
{
    public function __construct($message = "Connection timeout or no response exception", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
