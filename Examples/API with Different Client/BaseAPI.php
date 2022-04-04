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

    public function getPrefix()
    {
        return 'api';
    }

    /**
     * get default headers that will automatically bind with every request headers
     *
     * @return array
     */


    /**
     * some time we need check API authentication or validate default headers value,
     * with getDefaultHeaders() function we can check basic and token base authentication 
     * 
     */

    // Basic authentication   
    protected function getDefaultHeaders()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode(env('YOUR_API_AUTH_USER_NAME') . ':' . env('YOUR_API_AUTH_PASSWORD')),
        ];
    }

    /**
     * token base authentication 
     * 
     * protected function getDefaultHeaders()
     *   {
     *      return [
     *           'access_token' => $_ENV['ACCESS_TOKEN'] 
     *      ];
     *  }
     * 
     */
    
}
