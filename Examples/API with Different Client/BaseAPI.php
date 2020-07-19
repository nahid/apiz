<?php

namespace Examples;

use Apiz\AbstractApi;

abstract class BaseAPI extends AbstractApi
{
    protected function getClient()
    {
        return MyAwesomeClient();
    }
}
