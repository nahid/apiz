<?php

namespace Apiz\Exceptions;


class UnknownResponseClassException extends \Exception
{
    public function __construct($message = "Unknown response class exception", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}