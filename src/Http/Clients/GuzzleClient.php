<?php

namespace Apiz\Http\Clients;

use Apiz\Http\AbstractClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;

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

    public function send(...$args)
    {
        return $this->send(...$args);
    }
}
