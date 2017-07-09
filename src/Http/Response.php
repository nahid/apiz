<?php

namespace ApiManager\Http;

class Response
{
    protected $request;

    public $data = [];

    function __construct($request)
    {
        $this->request = $request;
        $this->data = $this->fetchData();
    }

    function __call($method, $args)
    {
        if (method_exists($this->request, $method)) {
            return call_user_func_array([$this->request, $method], $args);
        }
        return false;
    }

    protected function fetchData()
    {
        $header = explode(';', $this->request->getHeader('Content-Type')[0]);
        $contentType = $header[0];
        if ($contentType == 'application/json') {
            $contents = $this->request->getBody()->getContents();
            $data = json_decode($contents);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $data;
            }
            return $contents;
        }
        return false;
    }

    public function getData()
    {
        return $this->data;
    }
}