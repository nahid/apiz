<?php

namespace Apiz\Http;

use Apiz\Http\Clients\AbstractClient;
use Exception;
use Apiz\Exceptions\ClientNotDefinedException;
use Apiz\Exceptions\InvalidResponseClassException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Request
{
    /**
     * The main client to do all the magic
     *
     * @var AbstractClient
     */
    protected $client = null;

    /**
     * Base URL
     *
     * @var string
     */
    private $baseUrl = '';

    /**
     * URL prefix
     *
     * @var string
     */
    private $prefix = '';

    /**
     * Default headers options for request
     *
     * @var array
     */
    private $defaultHeaders = [];

    /**
     * Default Query options for request
     *
     * @var array
     */
    private $defaultQueries = [];

    /**
     * when need to skip default header make it true
     *
     * @var bool
     */
    private $shouldSkipDefaultHeader = false;

    /**
     * when need to skip default query make it true
     *
     * @var bool
     */
    private $shouldSkipDefaultQueries = false;

    /**
     * Options for http clients
     *
     * @var array
     */
    protected $options = [];

    /**
     * Request parameters
     *
     * @var array
     */
    protected $parameters = [];
    /**
     * @var RequestInterface
     */
    protected RequestInterface $psrRequest;

    public function __construct(AbstractClient $client = null)
    {
        if ($client) {
            $this->setClient($client);
        }
    }

    /**
     * @return AbstractClient
     * @throws ClientNotDefinedException
     */
    public function getClient()
    {
        if (!$this->client) {
            throw new ClientNotDefinedException();
        }

        return $this->client;
    }

    /**
     * @param AbstractClient $client
     */
    public function setClient(AbstractClient $client)
    {
        $this->client = $client;
    }

    /**
     * check if client is set
     *
     * @return string
     */
    public function hasClient()
    {
        return $this->client;
    }

    /**
     * get Base URL
     *
     * @return string
     */
    public function getBaseURL()
    {
        return trim($this->baseUrl, '/');
    }

    /**
     * set Base URL
     *
     * @param string $url
     */
    public function setBaseURL($url)
    {
        $this->baseUrl = $url;
    }

    /**
     * get url prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return trim($this->prefix, '/');
    }

    /**
     * set url prefix
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * get default headers that will automatically bind with every request headers
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return $this->defaultHeaders;
    }

    /**
     * set default headers that will automatically bind with every request headers
     *
     * @param array $headers
     */
    public function setDefaultHeaders($headers)
    {
        $this->defaultHeaders = $headers;
    }

    /**
     * get default queries that will automatically bind with every request
     *
     * @return array
     */
    protected function getDefaultQueries()
    {
        return $this->defaultQueries;
    }

    /**
     * set default queries that will automatically bind with every request
     *
     * @param array $queries
     */
    public function setDefaultQueries($queries)
    {
        $this->defaultQueries = $queries;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function getParameters($key = null)
    {
        if ($key) {
            return $this->parameters[$key];
        }

        return $this->parameters;
    }

    /**
     * @param array|string $parameters
     * @param string $key
     */
    public function setParameters($parameters, $key = null)
    {
        if ($key) {
            $this->parameters[$key] = $parameters;
        } else {
            $this->parameters = $parameters;
        }
    }

    /**
     * @param bool $action
     * @return Request
     */
    public function skipDefaultHeaders($action = true)
    {
        $this->shouldSkipDefaultHeader = $action;

        return $this;
    }

    /**
     * @param bool $action
     * @return Request
     */
    public function skipDefaultQueries($action = true)
    {
        $this->shouldSkipDefaultQueries = $action;

        return $this;
    }

    protected function mergeDefaultHeaders()
    {
        if (!$this->shouldSkipDefaultHeader) {
            if (isset($this->parameters['headers'])) {
                $this->parameters['headers'] = array_merge(
                    $this->defaultHeaders,
                    $this->parameters['headers']
                );
            } else {
                $this->parameters['headers'] = $this->defaultHeaders;
            }

            if (count($this->parameters['headers']) < 1) {
                unset($this->parameters['headers']);
            }
        }
    }

    protected function mergeDefaultQueries()
    {
        if (!$this->shouldSkipDefaultQueries) {
            if (isset($this->parameters['query'])) {
                $this->parameters['query'] = array_merge(
                    $this->defaultQueries,
                    $this->parameters['query']
                );
            } else {
                $this->parameters['query'] = $this->defaultQueries;
            }

            if (count($this->parameters['query']) < 1) {
                unset($this->parameters['query']);
            }
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @return RequestInterface
     * @throws ClientNotDefinedException
     */
    public function make($method, $uri)
    {
        $this->mergeDefaultHeaders();
        $this->mergeDefaultQueries();

        $fullPath = $this->getFullRequestPath($uri);

        $uriObject = $this->getClient()->getUri($fullPath);

        $this->psrRequest = $this->getClient()->getRequest($method, $uriObject);

        return $this->psrRequest;
    }

    public function getPsrRequest()
    {
        return $this->psrRequest;
    }

    /**
     * @param string $method
     * @param string $uri
     * @return ResponseInterface
     * @throws Exception
     */
    public function send($method, $uri)
    {
        $request = $this->make($method, $uri);

        return $this->sendByObject($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ClientNotDefinedException
     * @throws InvalidResponseClassException
     */
    public function sendByObject($request)
    {
        $response = $this->getClient()->send($request, $this->getParameters());

        if (!$this->getClient()->isValidResponse($response)) {
            throw new InvalidResponseClassException();
        }

        return $response;
    }

    /**
     * @param string $uri
     * @return string
     */
    public function getFullRequestPath($uri)
    {
        $uri = trim($uri, '/');
        $prefix = $this->getPrefix();
        $baseUrl = $this->getBaseURL();

        if ($prefix) {
            $uri = "{$prefix}/{$uri}";
        }

        return "{$baseUrl}/{$uri}";
    }

    public function reset()
    {
        $this->shouldSkipDefaultHeader = false;
        $this->shouldSkipDefaultQueries = false;
        $this->options = [];
        $this->parameters = [];
    }
}
