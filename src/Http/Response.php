<?php

namespace Apiz\Http;


class Response
{
    protected $response;
    protected $request;

    protected $contents = '';

    function __construct($response, $request)
    {
        $this->request = (object) $request;
        $this->response = $response;
        $this->contents = $this->fetchContents();
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
            $contents = json_decode($contents);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $contents;
            }
            return $contents;
        }
        return false;
    }

    public function fetchContents()
    {
        return $this->response->getBody()->getContents();
    }

    public function getMimeType()
    {
        $header = explode(';', $this->response->getHeader('Content-Type')[0]);
        $contentType = $header[0];
        return $contentType;
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function parseJson($array=false)
    {
        if ($this->getMimeType() == 'application/json') {
            $contents = $this->getContents();
            $contents = json_decode($contents, $array);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $contents;
            }
            return $contents;
        }
        return false;
    }

    public function parseXml()
    {
        libxml_use_internal_errors(true); // !!!

        if ($this->getMimeType() == 'application/xml') {
            $elem = simplexml_load_string($this->contents);
            if($elem !== false)
            {
                return $elem;
            }
            else
            {
                return libxml_get_errors();
            }
        }

        return false;

    }

    public function parseYaml()
    {
        if ($this->getMimeType() == 'application/x-yaml' || $this->getMimeType() == 'text/yaml') {
            return yaml_parse($this->getContents());
        }

    }

}