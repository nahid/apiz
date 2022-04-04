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

    /**
     * get default headers that will automatically bind with every request headers
     *
     * @return array
     */


    /**
     * some time we need check API  authentication we can check basic 
     * and token base authentication with getDefaultHeaders function 
     * 
     * token base authentication 
     * return [
     *          'access_token' => $_ENV['ACCESS_TOKEN'] 
     *      ];
     * 
     * Basic authentication   
     * return [
     *          'Authorization' => 'Basic ' . base64_encode('API_AUTH_USER' . ':' . 'API_AUTH_PASS'),
     *      ];
     */

    protected function getDefaultHeaders()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode(env('YOUR_API_AUTH_USER_NAME') . ':' . env('YOUR_API_AUTH_PASSWORD')),
        ];
    }
}
