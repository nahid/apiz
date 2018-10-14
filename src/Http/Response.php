<?php

namespace Apiz\Http;

use Apiz\Exceptions\NoResponseException;
use Nahid\JsonQ\Jsonq;

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

    protected $generator = null;


    /**
     * Store raw contents
     *
     * @var mixed|string
     */
    protected $contents = '';

    protected $jsonq = null;

    public function __construct($response, $request)
    {
        $this->request = (object) $request;
        if(is_null($response)) {
            throw new NoResponseException();
        }


        $this->response = $response;
        $this->contents = $this->fetchContents();
        $this->makeJsonQable();


        $generator = $request->details['generator'];
        if (class_exists($generator)) {
            $this->generator = new $generator($this);
        }

    }

    public function __invoke()
    {
        $jsonq = new Jsonq();

        return $jsonq->collect($this->parseJson(true));
    }

    public function __call($method, $args)
    {
        if (method_exists($this->response, $method)) {
            return call_user_func_array([$this->response, $method], $args);
        }

        if (method_exists($this->generator, $method)) {
            return call_user_func_array([$this->generator, $method], $args);
        }

        return false;
    }

    /**
     * execute any script after getting response
     *
     * @param callable $fn
     * @return null
     */
    public function afterResponse(callable $fn)
    {
        if (is_callable($fn)) {
            return $fn($this);
        }

        return null;
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

        if ($type == 'application/json' || $type == 'text/json' || $type == 'application/javascript') {
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
     * @return array|null
     */
    public function getMimeTypes()
    {
        $content_types = $this->response->getHeader('Content-Type');

        if (count($content_types) > 0) {
            return explode(';', $content_types[0]);
        }

        return null;
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

        if ( $type == 'application/json' || $type == 'text/json'|| $type == 'application/javascript' ) {
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

    /**
     * make Jsonq instance from response
     */
    protected function makeJsonQable()
    {
        $json = $this->parseJson(true);

        if ($json) {
            $jsonq = new Jsonq();
            $this->jsonq = $jsonq->collect($json);
        }
    }

    /**
     * check is the response is JSON
     *
     * @return bool
     */
    public function isJson()
    {
        if ($this->jsonq instanceof Jsonq) {
            return true;
        }

        return false;
    }

    /**
     * get the response body size
     *
     * @return int
     */
    public function size()
    {
        $lengths = $this->response->getHeader('Content-Length');

        if (count($lengths) > 0) {
            return (int) $lengths[0];
        }

        return 0;
    }

    /**
     * check is response empty
     * 
     * @return bool
     */
    public function isEmpty()
    {
        if ($this->size()) {
            return true;
        }

        return false;
    }

    /**
     * return JsonQ instance from response
     * 
     * @return Jsonq|null
     */
    public function jsonq()
    {
        if ($this->isJson()) {
            return clone $this->jsonq;
        }

        return (new Jsonq())->collect([]);
    }
}
