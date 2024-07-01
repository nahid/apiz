<?php

namespace Apiz\Http;

use Apiz\Http\Clients\AbstractClient;
use Apiz\Http\Clients\GuzzleClient;
use Exception;
use Apiz\Exceptions\ClientNotDefinedException;
use Apiz\Exceptions\InvalidResponseClassException;
use GuzzleHttp\Psr7\MultipartStream;
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
    protected $body = [];

    /**
     * Query parameters
     *
     * @var array
     */
    protected $queryParams = [];

    protected $headers = [];

    protected $contentType = 'application/x-www-form-urlencoded';

    /**
     * @var RequestInterface
     */
    protected RequestInterface $psrRequest;

    public function __construct(AbstractClient $client = null)
    {
        if (!$client) {
            $client = new GuzzleClient();
        }

        $this->setClient($client);
    }

    /**
     * @return AbstractClient
     * @throws ClientNotDefinedException
     */
    public function getClient(): AbstractClient
    {
        if (!$this->client) {
            throw new ClientNotDefinedException();
        }

        return $this->client;
    }

    /**
     * @param AbstractClient $client
     */
    public function setClient(AbstractClient $client): void
    {
        $this->client = $client;
    }

    /**
     * check if client is set
     *
     * @return bool
     */
    public function hasClient(): bool
    {
        return !!$this->client;
    }

    /**
     * get Base URL
     *
     * @return string
     */
    public function getBaseURL(): string
    {
        return trim($this->baseUrl, '/');
    }

    /**
     * set Base URL
     *
     * @param string $url
     */
    public function setBaseURL(string $url): void
    {
        $this->baseUrl = $url;
    }

    /**
     * get url prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return trim($this->prefix, '/');
    }

    /**
     * set url prefix
     *
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * get default headers that will automatically bind with every request headers
     *
     * @return array
     */
    protected function getDefaultHeaders(): array
    {
        return $this->defaultHeaders;
    }

    /**
     * set default headers that will automatically bind with every request headers
     *
     * @param array $headers
     */
    public function setDefaultHeaders(array $headers): void
    {
        $this->defaultHeaders = $headers;
    }

    /**
     * get default queries that will automatically bind with every request
     *
     * @return array
     */
    protected function getDefaultQueries(): array
    {
        return $this->defaultQueries;
    }

    /**
     * set default queries that will automatically bind with every request
     *
     * @param array $queries
     */
    public function setDefaultQueries(array $queries): void
    {
        $this->defaultQueries = $queries;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param string $name
     * @param mixed $option
     */
    public function setOption(string $name, $option): void
    {
        $this->options[$name] = $option;
    }

    /**
     * @return mixed
     */
    public function getBodyContents()
    {
        return $this->body;
    }

    /**
     * @param array $contents
     * @param ?string $key
     */
    public function setBodyContents($contents): void
    {
        $this->body = $contents;
    }

    public function setBodyParam(string $key, $value): void
    {
        if (!is_array($this->body)) {
            $this->body = [];
        }

        $this->body[$key] = $value;
    }

    public function setBodyParams(array $params): void
    {
        $this->body = $params;
    }

    public function setBodyMultipart(array $value): void
    {
        $this->setContentType('multipart/form-data');

        if (!is_array($this->body)) {
            $this->body = [];
        }

        $this->body[] = $value;
    }

    /**
     * @param bool $action
     * @return Request
     */
    public function skipDefaultHeaders(bool $action = true): self
    {
        $this->shouldSkipDefaultHeader = $action;

        return $this;
    }

    /**
     * @param bool $action
     * @return Request
     */
    public function skipDefaultQueries(bool $action = true): self
    {
        $this->shouldSkipDefaultQueries = $action;

        return $this;
    }

    public function setContentType(string $type): void
    {
        $this->contentType = $type;

        if ($type == 'multipart/form-data') {
            return;
        }

        $this->setHeader('Content-Type', $type);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $key): ?string
    {
        return $this->headers[$key] ?? null;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function setHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    public function hasHeader(string $key): bool
    {
        return isset($this->headers[$key]);
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getQueryParam(string $key): ?string
    {
        return $this->queryParams[$key] ?? null;
    }

    public function setQueryParams(array $queryParams): void
    {
        $this->queryParams = $queryParams;
    }

    public function setQueryParam(string $key, string $value): void
    {
        $this->queryParams[$key] = $value;
    }

    public function hasQueryParam(string $key): bool
    {
        return isset($this->queryParams[$key]);
    }

    protected function mergeDefaultHeaders(): void
    {
        if ($this->shouldSkipDefaultHeader) {
            return;
        }

        $this->setHeaders(array_merge(
            $this->defaultHeaders,
            $this->getHeaders()
        ));
    }

    protected function mergeDefaultQueries(): void
    {
        if ($this->shouldSkipDefaultQueries) {
            return;
        }

        $this->setQueryParams(array_merge(
            $this->defaultQueries,
            $this->getQueryParams()
        ));
    }

    /**
     * @param string $method
     * @param string $uri
     * @return RequestInterface
     * @throws ClientNotDefinedException
     */
    public function make(string $method, string $uri): RequestInterface
    {
        $this->mergeDefaultHeaders();
        $this->mergeDefaultQueries();

        $fullPath = $this->getFullRequestPath($uri);
        $uriObject = $this->getClient()->getUri($fullPath);

        $body = $this->getBodyContents();

        if (is_array($body) && $this->contentType == 'multipart/form-data') {
            $body = new MultipartStream($body);
        }

        $this->psrRequest = $this->getClient()->getRequest($method, $uriObject, $this->getHeaders(), $body);

        return $this->psrRequest;
    }

    public function getPsrRequest(): RequestInterface
    {
        return $this->psrRequest;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ClientNotDefinedException
     * @throws InvalidResponseClassException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $response = $this->getClient()->send($request, $this->getOptions());

        if (!$this->getClient()->isValidResponse($response)) {
            throw new InvalidResponseClassException();
        }

        return $response;
    }

    /**
     * @param string $uri
     * @return string
     */
    public function getFullRequestPath(string  $uri): string
    {
        $uri = trim($uri, '/');
        $prefix = $this->getPrefix();
        $baseUrl = $this->getBaseURL();

        if ($prefix) {
            $uri = "{$prefix}/{$uri}";
        }

        $params = '';

        if (!empty($this->queryParams)) {
            $params = '?' . http_build_query($this->queryParams);
        }

        return "{$baseUrl}/{$uri}{$params}";
    }

    public function reset(): void
    {
        $this->shouldSkipDefaultHeader = false;
        $this->shouldSkipDefaultQueries = false;
        $this->options = [];
        $this->body = [];
        $this->queryParams = [];
        $this->headers = [];
        $this->contentType = 'application/x-www-form-urlencoded';
    }
}
