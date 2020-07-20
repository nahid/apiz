<?php

namespace Examples;

use Apiz\AbstractApi;

abstract class BaseAPI extends AbstractApi
{
    public function __construct()
    {
        parent::__construct();

        $this->setClient(new MyAwesomeClient());

        $this->setBaseURL('https://reqres.in');
        $this->setPrefix('api');
    }
}
