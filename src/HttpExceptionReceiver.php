<?php

namespace Apiz;

use GuzzleHttp\Psr7\Response;

class HttpExceptionReceiver
{
    protected $exceptions;
    protected $statusCode;

    public function __construct(Response $response, $exceptions = [])
    {
        $this->statusCode= (int) $response->getStatusCode();
        $this->exceptions= $exceptions;

        $this->throwExceptions();
    }


    protected function throwExceptions()
    {
        if (array_key_exists($this->statusCode, $this->exceptions)) {
            throw new $this->exceptions[$this->statusCode]();
        }
    }
}