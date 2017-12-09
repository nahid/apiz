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
    public $http;

    public function __construct($base_url, $options  = [])
    {
        $default = ['base_uri' => $base_url, 'timeout' => 30.0];
        $opts = array_merge($default, $options);
        $this->http = new Client($opts);
    }
}
