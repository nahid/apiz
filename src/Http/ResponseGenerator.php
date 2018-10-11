<?php

namespace Apiz\Http;

abstract class ResponseGenerator
{
    protected $response;

    protected $pipes = [];

    protected $pipeResponse = null;


    public function __construct(Response $response)
    {
        $this->response = $response;
        $this->pipe();
    }

    protected function pipe()
    {
        $param = [];
        foreach($this->pipes as $pipe) {
            $method = 'pipe' . ucfirst($pipe);

            if (method_exists($this, $method)) {
                if (count($param) == 0) {
                    $param = $this->response;
                }

                $this->pipeResponse = call_user_func_array([$this, $method], [$param]);
                $param = $this->pipeResponse;
            }
        }
    }

    protected function jsonq()
    {
        $response = $this->response;
        return $response();
    }

    public function getPipedData()
    {
        return $this->pipeResponse;
    }
}