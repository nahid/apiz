<?php

namespace Apiz\Http\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class GuzzleClient extends AbstractClient
{
    /**
     * @inheritDoc
     */
    public function getRequestClass()
    {
        return Request::class;
    }

    /**
     * @inheritDoc
     */
    public function getResponseClass()
    {
        return Response::class;
    }

    /**
     * @inheritDoc
     */
    public function getUriClass()
    {
        return Uri::class;
    }

    /**
     * @param mixed ...$args
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function send(...$args)
    {
        $client = new Client($this->config);

        return $client->send(... $args);
    }
}
