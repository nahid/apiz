<?php

namespace Apiz\Http\Clients;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

abstract class AbstractClient
{

    /**
     * @var array
     */
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }
    /**
     * @return string
     */
    protected abstract function getRequestClass();

    /**
     * @return string
     */
    protected abstract function getResponseClass();

    /**
     * @return string
     */
    protected abstract function getUriClass();

    /**
     * @param array $args
     * @return ResponseInterface
     * @throws Exception
     */
    public abstract function send(...$args);

    /**
     * @param mixed ...$args
     * @return RequestInterface
     */
    public function getRequest(...$args)
    {
        $class = $this->getRequestClass();

        return new $class(...$args);
    }

    /**
     * @param mixed ...$args
     * @return ResponseInterface
     */
    public function getResponse(...$args)
    {
        $class = $this->getResponseClass();

        return new $class(...$args);
    }

    /**
     * @param mixed ...$args
     * @return UriInterface
     */
    public function getUri(...$args)
    {
        $class = $this->getUriClass();

        return new $class(...$args);
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    public function isValidResponse(ResponseInterface $response)
    {
        $responseClass = $this->getResponseClass();

        if ($response instanceof $responseClass) {
            return true;
        }

        return false;
    }
}
