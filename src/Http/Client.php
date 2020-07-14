<?php

namespace Apiz\Http;

use Apiz\Http\Clients\GuzzleClient;

class ClientManager
{
    public static function getClient()
    {
        return new GuzzleClient();
    }
}
