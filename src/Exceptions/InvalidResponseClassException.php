<?php

namespace Apiz\Exceptions;

use Exception;
use Throwable;

class InvalidResponseClassException extends Exception
{
    public function __construct($message = "Invalid Response Class", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
