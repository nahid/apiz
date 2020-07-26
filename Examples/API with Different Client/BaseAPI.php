<?php

namespace Examples;

use Apiz\AbstractApi;

abstract class BaseAPI extends AbstractApi
{
    public function __construct()
    {
        parent::__construct();

        // Just pass an instance of your own PSR7 supported client like this
        $this->setClient(new MyAwesomeClient());
    }

    /**
     * @inheritDoc
     */
    protected function getBaseURL()
    {
        return 'https://reqres.in';
    }

    protected function getPrefix()
    {
        return 'api';
    }
}
