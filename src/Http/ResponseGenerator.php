<?php

namespace Apiz\Http;

abstract class ResponseGenerator
{
    protected $response;


    public function __construct(Response $response)
    {
        $this->response = $response;
    }


    /**
     * create JsonQ instance
     *
     * @return \Nahid\JsonQ\Jsonq|null
     */
    protected function jsonq()
    {
        $response = $this->response;
        return $response();
    }
}