<?php

namespace Apiz\Http;

use GuzzleHttp\Client;

class  Request
{
    var $http;

    function __construct(string $base_url)
    {
        $this->http = new Client(['base_uri' => $base_url, 'timeout' => 2.0]);
    }

}