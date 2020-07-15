<?php

namespace Apiz\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

abstract class AbstractClient
{
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
     */
    protected abstract function send(...$args);

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
}
