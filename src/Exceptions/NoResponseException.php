<?php

namespace Apiz\Exceptions;


class NoResponseException extends \Exception
{
    public function __construct($message = "Connection timeout or no response exception", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}