<?php

namespace Apiz\Http;

use Apiz\Exceptions\NoResponseException;

class Response
{
    /**
     * store response object
     *
     * @var object
     */
    protected $response;


    /**
     * Store request details
     *
     * @var object
     */
    protected $request;


    /**
     * Store raw contents
     *
     * @var mixed|string
     */
    protected $contents = '';

    public function __construct($response, $request)
    {
        $this->request = (object) $request;

        if(is_null($response)) {
            throw new NoResponseException();
        }

        $this->response = $response;
        $this->contents = $this->fetchContents();
    }

    public function __call($method, $args)
    {
        if (method_exists($this->response, $method)) {
            return call_user_func_array([$this->response, $method], $args);
        }
        return false;
    }


    /**
     * Automatically parse response contents based on mime type
     *
     * @return array|bool|mixed|\SimpleXMLElement|string
     */
    public function autoParse()
    {
        $type = $this->getMimeType();
        $contents = '';

        if ($type == 'application/json' || $type == 'text/json') {
            $contents = $this->parseJson();
        } elseif ($type == 'application/xml' || $type == 'text/xml') {
            $contents = $this->parseXml();
        } elseif ($type == 'application/x-yaml' || $type == 'text/yaml') {
            $contents = $this->parseYaml();
        } else {
            $contents = $this->getContents();
        }

        return $contents;
    }

    /**
     * Fetch response raw contents
     *
     * @return mixed
     */
    private function fetchContents()
    {
        return $this->response->getBody()->getContents();
    }

    /**
     * Get response data mime types
     *
     * @return array
     */
    public function getMimeTypes()
    {
        return explode(';', $this->response->getHeader('Content-Type')[0]);
    }

    /**
     * Get response data mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        $header = $this->getMimeTypes();
        $contentType = $header[0];
        return $contentType;
    }

    /**
     * Getter for contents
     *
     * @return mixed|string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Get requests details
     *
     * @return object
     */
    public function getRequests()
    {
        return $this->request;
    }

    /**
     * Parse raw contents if JSON
     *
     * @param bool $array
     * @return bool|mixed|string
     */
    public function parseJson($array = false)
    {
        $type = $this->getMimeType();

        if ( $type == 'application/json' || $type == 'text/json' ) {
            $contents = $this->getContents();
            $contents = json_decode($contents, $array);
            if ( json_last_error() == JSON_ERROR_NONE ) {
                return $contents;
            }
        }

        return false;

    }

    /**
     * Parse raw contents if XML
     *
     * @return array|bool|\SimpleXMLElement
     */
    public function parseXml()
    {
        libxml_use_internal_errors(true);
        
        $type = $this->getMimeType();
        if ( $type == 'application/xml' || $type == 'text/xml' ) {
            $elem = simplexml_load_string($this->contents);
            if ( $elem !== false ) {
                return $elem;
            } else {
                return libxml_get_errors();
            }
        }

        return false;
    }

    /**
     * Parse raw contents if Yaml
     *
     * @return mixed
     */
    public function parseYaml()
    {
        $type = $this->getMimeType();

        if ( $type == 'application/x-yaml' || $type == 'text/yaml' ) {
            return yaml_parse($this->getContents());
        }

        return false;
    }
}
