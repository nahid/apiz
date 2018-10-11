<?php

namespace Apiz\Http;

abstract class ResponseGenerator
{
    protected $response;

    protected $fires = [];


    public function __construct(Response $response)
    {
        $this->response = $response;
        $this->fire();
    }

    protected function fire()
    {
        foreach($this->fires as $fire) {
            if (method_exists($this, $fire)) {
                call_user_func_array([$this, $fire], []);
            }
        }
    }

    protected function jsonq()
    {
        $response = $this->response;
        return $response();
    }
}