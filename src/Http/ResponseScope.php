<?php

namespace Apiz\Http;

abstract class ResponseScope
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

    protected function push($method)
    {
        array_pull($this->fires, $method);
        return $this;
    }

    protected function jsonq()
    {
        $response = $this->response;
        return $response();
    }
}