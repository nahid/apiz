<?php

namespace Apiz\Exceptions;

use Exception;

class InvalidResponseClassException extends Exception
{
    public function __construct($message = "Invalid Response Class", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
