<?php

namespace ApiManager;


use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use ApiManager\Http\Request;
use ApiManager\Http\Response;


abstract class AbstractApi
{
    protected $baseUrl = false;
    protected $prefix = '';
    protected $url = '';
    protected $request = [];
    protected $defaultHeaders = [];
    protected $client;
    protected $parameters= [];
    private $requestMethods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'HEAD',
        'OPTIONS',
        'PATCH'
    ];
    function __construct()
    {
        if (!$this->baseUrl) {
            $this->baseUrl = $this->setBaseUrl();
        }

        $this->defaultHeaders = $this->setDefaultHeaders();

        $this->client = new Request($this->baseUrl);
    }


    protected function setBaseUrl()
    {
        return false;
    }

    protected function setDefaultHeaders():array
    {
        return [];
    }

    public function __call($func, $params)
    {
        $method = strtoupper($func);
        if (in_array($method, $this->requestMethods)) {
            $parameters[] = $method;
            $parameters[] = $params[0];
            $content = call_user_func_array([$this, 'makeMethodRequest'], $parameters);


            return $content;
        }
    }

    abstract protected function getAccessToken():string;

    public function formParams($params = array())
    {
        if (is_array($params)) {
            $this->parameters['form_params'] = $params;
            return $this;
        }
        return false;
    }
    public function headers($params = array())
    {
        if (is_array($params)) {
            $this->parameters['headers'] = $params;
            return $this;
        }
        return false;
    }
    public function query($params = array())
    {
        if (is_array($params)) {
            $this->parameters['query'] = $params;
            return $this;
        }
        return false;
    }
    public function makeMethodRequest($method, $uri)
    {
        $uri = $this->trimString($this->prefix) . '/' . $this->trimString($uri);

        $this->parameters['timeout'] = 60;



        if (isset($this->parameters['headers'])) {
            $this->parameters['headers'] = array_merge($this->defaultHeaders , $this->parameters['headers']);
        } else {
            $this->parameters['headers'] = $this->defaultHeaders;
        }

        $this->request = [
            'url' => $this->trimString($this->baseUrl) . '/' . $uri,
            'method' => $method,
            'parameters' => $this->parameters
        ];

        try {
            return $response = new Response($this->client->http->request($method, $uri, $this->parameters), $this->request);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        } catch (ClientException $e) {
            $response = $e->getResponse();
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        } catch (ServerException $e) {
            $response = $e->getResponse();
        }

        return new Response($response, $this->request);
    }


    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getRequestData()
    {
        return (object) $this->request;
    }


    protected function trimString($string)
    {
        return rtrim(ltrim($string, '/'), '/');
    }



}