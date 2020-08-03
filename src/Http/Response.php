<?php

namespace Apiz\Http;

use Exception;
use Nahid\QArray\QueryEngine;
use Apiz\Exceptions\NoResponseException;
use Apiz\QueryBuilder;
use Apiz\Utilities\Parser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

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
     * @var Request
     */
    protected $request;

    /**
     * Store raw contents
     *
     * @var mixed|string
     */
    protected $rawContent = '';

    /**
     * instance of QueryBuilder
     *
     * @var null|QueryBuilder
     */
    protected $queryBuilder = null;

    /**
     * Response constructor.
     *
     * @param Request $request
     * @param ResponseInterface $response
     * @throws NoResponseException
     */
    public function __construct(Request $request, ResponseInterface $response)
    {
        $this->setRequest($request);
        $this->setResponse($response);

        $this->rawContent = $this->fetchContents();
    }

    /**
     * This is to make the response invokable and behave properly to Query calls
     * e.g. With the $response, user can now call $response()->from('node')->get();
     *
     * @return QueryEngine
     * @throws Exception
     */
    public function __invoke()
    {
        return $this->query();
    }

    public function __toString()
    {
        return (string) $this->rawContent;
    }

    /**
     * Get requests details
     *
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    protected function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param ResponseInterface $response
     * @throws NoResponseException
     */
    protected function setResponse(ResponseInterface $response)
    {
        if(is_null($response)) {
            throw new NoResponseException();
        }

        $this->response = $response;
    }

    /**
     * Automatically parse response contents based on mime type
     *
     * @return array|bool|mixed|SimpleXMLElement|string
     */
    public function autoParse()
    {
        return Parser::parseByMimeType($this->getContents(), $this->getMimeType());
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

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * @param $name
     * @return string[]
     */
    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }

    /**
     * @param $name
     * @return string
     */
    public function getHeaderLine($name)
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * @return StreamInterface
     */
    public function getBody()
    {
        return $this->response->getBody();
    }

    /**
     * Get response data mime types
     *
     * @return array
     */
    public function getMimeTypes()
    {
        $content_types = $this->response->getHeader('Content-Type');

        if (count($content_types) > 0) {
            return explode(';', $content_types[0]);
        }

        return [];
    }

    /**
     * Get response data mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        $header = $this->getMimeTypes();
        if (count($header) > 0) {
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
        return $this->rawContent;
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
        return (bool) $this->size();
    }

    /**
     * make QueryBuilder instance from response
     */
    protected function initQueryBuilder()
    {
        if (is_null($this->queryBuilder)) {
            $this->queryBuilder = new QueryBuilder();

            $parsedContent = $this->autoParse();

            if ($parsedContent) {
                $this->queryBuilder = $this->queryBuilder->collect($parsedContent);
            }
        }
    }

    /**
     * return QueryBuilder instance from response
     *
     * @return QueryEngine
     * @throws Exception
     */
    public function query()
    {
        $this->initQueryBuilder();

        return $this->queryBuilder;
    }

    /**
     * @return QueryEngine
     */
    public function reset()
    {
        return $this->queryBuilder->reset(null, true);
    }
}
