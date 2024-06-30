<?php

namespace Apiz\Http\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleClient extends AbstractClient
{
    /**
     * @inheritDoc
     * @return string
     */
    public function getRequestClass(): string
    {
        return Request::class;
    }

    /**
     * @inheritDoc
     */
    public function getResponseClass(): string
    {
        return Response::class;
    }

    /**
     * @inheritDoc
     */
    public function getUriClass(): string
    {
        return Uri::class;
    }

    /**
     * @param mixed ...$args
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function send(...$args): ResponseInterface
    {
        $client = new Client($this->config);

        return $client->send(... $args);
    }
}
