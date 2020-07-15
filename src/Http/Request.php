<?php

namespace Apiz\Http;

use GuzzleHttp\Client;

class Request
{
    /**
     * Guzzle http client object
     *
     * @var Client
     */
    public $client;

    public function __construct(Client $client = null, $opts = [])
    {
        if (!isset($opts['timeout'])) {
            $opts['timeout'] = 30.0;
        }

        if (is_null($client)) {
            $client = new Client($opts);
        }

        $this->client = $client;
    }
}
