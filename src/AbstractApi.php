<?php

namespace Apiz;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Apiz\Http\Request;
use Apiz\Http\Response;


abstract class AbstractApi
{
    protected $baseUrl = '';
    protected $prefix = '';
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
        $this->baseUrl = $this->setBaseUrl();

        $this->defaultHeaders = $this->setDefaultHeaders();

        //$this->parameters['multipart'] = [];

        $this->client = new Request($this->baseUrl);
    }


    abstract protected function setBaseUrl():string;

    protected function getAccessToken():string
    {
        return '';
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

    public function allowRedirects($params = [])
    {
        if (is_array($params)) {
            $this->parameters['allow_redirects'] = $params;
            return $this;
        }
        return false;
    }

    public function auth($username, $password, $opts = [])
    {
        $params = [$username, $password];

        if (is_array($opts)) {
            $params = array_merge($params, $opts);
        }

        $this->parameters['auth'] = $params;
        return $this;
    }

    public function body($contents)
    {
        $this->parameters['body'] = $contents;
        return $this;
    }

    public function json($params = [])
    {
        if (is_array($params)) {
            $this->parameters['json'] = $params;
            return $this;
        }
        return false;
    }

    public function file($name, $file, $filename, $headers = [])
    {
        $params = [];

        if (file_exists($file)) {
            $contents = fopen($file, 'r');

            $params = [
                'name'  => $name,
                'contents'  => $contents,
                'filename'  => $filename,
                'headers'   => $headers
            ];
         }

        $this->parameters['multipart'][] = $params;
        return $this;
    }

    public function attach($name, $contents, $filename, $headers = [])
    {
        $params = [
            'name'  => $name,
            'contents'  => $contents,
            'filename'  => $filename,
            'headers'   => $headers
        ];


        $this->parameters['multipart'][] = $params;
        return $this;
    }

    public function params($options = [])
    {
        $this->parameters = $options;
        return $this;
    }


    protected function makeMethodRequest($method, $uri)
    {
        $uri = trim($this->prefix, '/') . '/' . trim($uri, '/');

        $this->parameters['timeout'] = 60;

        if (isset($this->parameters['headers'])) {
            $this->parameters['headers'] = array_merge($this->defaultHeaders , $this->parameters['headers']);
        } else {
            $this->parameters['headers'] = $this->defaultHeaders;
        }

        $this->request = [
            'url' => trim($this->baseUrl, '/') . '/' . $uri,
            'method' => $method,
            'parameters' => $this->parameters
        ];

        $request = new Psr7Request($method, $uri);
        $request->info = $this->request;

        try {
            $response = $this->client->http->send($request, $this->parameters);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        } catch (ClientException $e) {
            $response = $e->getResponse();
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        } catch (ServerException $e) {
            $response = $e->getResponse();
        }

        return new Response($response, $request);
    }


    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

}