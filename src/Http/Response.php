<?php

namespace ApiManager\Http;

class Response
{
    protected $response;
    protected $request;

    public $data = [];

    function __construct($response, $request)
    {
        $this->request = (object) $request;
        $this->response = $response;
        $this->data = $this->fetchDataFromJson();
    }

    function __call($method, $args)
    {
        if (method_exists($this->response, $method)) {
            return call_user_func_array([$this->response, $method], $args);
        }
        return false;
    }

    public function fetchDataFromJson()
    {
        $header = explode(';', $this->response->getHeader('Content-Type')[0]);
        $contentType = $header[0];
        if ($contentType == 'application/json') {
            $contents = $this->response->getBody()->getContents();
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