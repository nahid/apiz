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
    protected $baseUrl = '';
    protected $client;
    protected $parameters= [];
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $scopes = [];
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
        $this->client = new Request($this->baseUrl);
    }
    public function __call($func, $params)
    {
        $method = strtoupper($func);
        if (in_array($method, $this->requestMethods)) {
            $parameters[] = $method;
            $parameters[] = $params[0];
            $content = call_user_func_array([$this, 'makeMethodRequest'], $parameters);

            if ($content->getStatusCode() == 200 && isset($content->data)) {
                $this->data = $content->data;
            }

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
        $this->parameters['timeout'] = 60;
        $defaultHeaders = [
            'User-Agent'=>$_SERVER['HTTP_USER_AGENT']
        ];

        if ($this->getAccessToken() !== false) {
            array_push($defaultHeaders, ['Authorization'=> 'Bearer ' .  $this->getAccessToken()]);
        }

        if ($method == 'GET') {
            if (isset($this->parameters['headers'])) {
                $this->parameters['headers'] = array_merge($defaultHeaders , $this->parameters['headers']);
            } else {
                $this->parameters['headers'] = $defaultHeaders;
            }

        }
        try {
            return $response = new Response($this->client->http->request($method, $uri, $this->parameters));
        } catch (RequestException $e) {
            return $e->getResponse();
        } catch (ClientException $e) {
            return $e->getResponse();
        } catch (BadResponseException $e) {
            return $e->getResponse();
        } catch (ServerException $e) {
            return $e->getResponse();
        }
    }


}