<?php

namespace Apiz\Http;

use Apiz\Exceptions\NoResponseException;
use Apiz\QueryBuilder;
use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * store response object
     *
     * @var ResponseInterface
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

    /**
     * instance of QueryBuilder
     *
     * @var null|QueryBuilder
     */
    protected $queries = null;

    /**
     * Response constructor.
     *
     * @param Request $request
     * @param ResponseInterface $response
     * @throws NoResponseException
     */
    public function __construct(Request $request, ResponseInterface $response)
    {
        $this->request = $request;
        if(is_null($response)) {
            throw new NoResponseException();
        }

        $this->response = $response;
        $this->contents = $this->fetchContents();
        $this->makeQueryable();
    }

    public function __invoke()
    {
       return $this->query()->reset(null, true);
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
            $contents = $this->parseJson(true);
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
        return $this->getBody()->getContents();
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }

    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }

    public function getHeaderLine($name)
    {
        return $this->response->getHeaderLine($name);
    }

    public function getBody()
    {
        return $this->response->getBody();
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
        if ($header) {
            return $header[0];
        }

        return null;
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
     * @return Request
     */
    public function getRequest()
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
                return $this->xml2array($elem);
            } else {
                return libxml_get_errors();
            }
        }

        return false;
    }

    protected function xml2array($data)
    {
        $out = [];
        foreach ( (array) $data as $index => $node ) {
            $out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
        }

        return $out;
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
     * make QueryBuilder instance from response
     */
    protected function makeQueryable()
    {
        $array = $this->autoParse();

        if ($array) {
            $this->queries = (new QueryBuilder())->collect($array);
        }
    }

    /**
     * get the response body size
     *
     * @return int
     */
    public function size()
    {
        $lengths = $this->getHeader('Content-Length');

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
     * return QueryBuilder instance from response
     *
     * @param $node
     * @return QueryBuilder|null
     * @throws \Exception
     */
    public function query($node = null)
    {
        if (!$this->queries instanceof QueryBuilder) {
            $this->queries = new QueryBuilder();
        }

        if (!is_null($node)) {
            $this->queries->from($node);
        }

        return $this->queries;
    }
}
