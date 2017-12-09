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
    /**
     * Options for guzzle clients
     *
     * @var array
     */
    protected $options = [];

    /**
     * guzzle base URL
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * URL prefix
     *
     * @var string
     */
    protected $prefix = '';


    /**
     * this variable contains request details
     *
     * @var array
     */
    protected $request = [];


    /**
     * Default headers options for request
     *
     * @var array
     */
    protected $defaultHeaders = [];

    /**
     * Guzzle http object
     *
     * @var Request
     */
    protected $client;

    /**
     * Request parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * All supported HTTP verbs
     *
     * @var array
     */
    private $requestMethods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'HEAD',
        'OPTIONS',
        'PATCH'
    ];


    public function __construct()
    {
        $this->baseUrl = $this->setBaseUrl();

        $this->defaultHeaders = $this->setDefaultHeaders();

        $this->client = new Request($this->baseUrl, $this->options);
    }


    /**
     * set base URL for guzzle client
     *
     * @return string
     */
    abstract protected function setBaseUrl();

    /**
     * Set access token retrieval method
     *
     * @return string
     */
    protected function getAccessToken()
    {
        return '';
    }

    /**
     * set default headers that will automatically bind with every request headers
     *
     * @return array
     */
    protected function setDefaultHeaders()
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


    /**
     * set form parameters or form data for POST, PUT and PATCH request
     *
     * @param array $params
     * @return $this|bool
     */
    protected function formParams($params = array())
    {
        if (is_array($params)) {
            $this->parameters['form_params'] = $params;
            return $this;
        }
        return false;
    }

    /**
     * set request headers
     *
     * @param array $params
     * @return $this|bool
     */
    protected function headers($params = array())
    {
        if (is_array($params)) {
            $this->parameters['headers'] = $params;
            return $this;
        }
        return false;
    }


    /**
     * set query parameters
     *
     * @param array $params
     * @return $this|bool
     */
    protected function query($params = array())
    {
        if (is_array($params)) {
            $this->parameters['query'] = $params;
            return $this;
        }
        return false;
    }

    /**
     * Add allow redirects param
     *
     * @param array $params
     * @return $this|bool
     */
    protected function allowRedirects($params = [])
    {
        if (is_array($params)) {
            $this->parameters['allow_redirects'] = $params;
            return $this;
        }
        return false;
    }

    /**
     * Set basic auth options
     *
     * @param       $username
     * @param       $password
     * @param array $opts
     * @return $this
     */
    protected function auth($username, $password, $opts = [])
    {
        $params = [$username, $password];

        if (is_array($opts)) {
            $params = array_merge($params, $opts);
        }

        $this->parameters['auth'] = $params;
        return $this;
    }


    /**
     * Set request body
     *
     * @param string|blob|array
     * @return $this|bool
     */
    protected function body($contents)
    {
        if (is_array($contents)) {
            $this->headers([
                'Content-Type'=>'application/json'
            ]);

            $contents = json_encode($contents);
        }
        $this->parameters['body']   = $contents;
        return $this;
    }


    /**
     * Set request param as JSON
     *
     * @param array $params
     * @return $this|bool
     */
    protected function json($params = [])
    {
        if (is_array($params)) {
            $this->parameters['json'] = $params;
            return $this;
        }
        return false;
    }

    /**
     * Send file to the request
     *
     * @param       $name
     * @param       $file
     * @param       $filename
     * @param array $headers
     * @return $this
     */
    protected function file($name, $file, $filename, $headers = [])
    {
        $params = [];

        if (file_exists($file)) {
            $contents = fopen($file, 'r');

            $params = [
                'name' => $name,
                'contents' => $contents,
                'filename' => $filename,
                'headers' => $headers
            ];
        }

        $this->parameters['multipart'][] = $params;
        return $this;
    }

    /**
     * Attach a raw content with request
     *
     * @param       $name
     * @param       $contents
     * @param       $filename
     * @param array $headers
     * @return $this
     */
    protected function attach($name, $contents, $filename, $headers = [])
    {
        $params = [
            'name' => $name,
            'contents' => $contents,
            'filename' => $filename,
            'headers' => $headers
        ];


        $this->parameters['multipart'][] = $params;
        return $this;
    }

    /**
     * Set all parameters from this single options
     *
     * @param array $options
     * @return $this
     */
    protected function params($options = [])
    {
        $this->parameters = $options;
        return $this;
    }


    /**
     * Make all request from here
     *
     * @param string $method
     * @param string $uri
     * @return Response
     */
    protected function makeMethodRequest($method, $uri)
    {
        if (!empty($this->prefix)) {
            $this->prefix = trim($this->prefix, '/') . '/';
        }
        $uri = $this->prefix . trim($uri, '/');

        //$this->parameters['timeout'] = 60;

        if (isset($this->parameters['headers'])) {
            $this->parameters['headers'] = array_merge($this->defaultHeaders, $this->parameters['headers']);
        } else {
            $this->parameters['headers'] = $this->defaultHeaders;
        }

        if (count($this->parameters['headers']) < 1) {
            unset($this->parameters['headers']);
        }

        $this->request = [
            'url' => trim($this->baseUrl, '/') . '/' . $uri,
            'method' => $method,
            'parameters' => $this->parameters
        ];

        $request = new Psr7Request($method, $uri);
        $request->details = $this->request;

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


        $resp = new Response($response, $request);
        $this->resetObjects();
        return $resp;
    }


    /**
     * Get base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get Guzzle http client object
     *
     * @return \GuzzleHttp\Client
     */
    public function getGuzzleClient()
    {
        return $this->client->http;
    }

    /**
     * Reset this class objects
     */
    protected function resetObjects()
    {
        $skip = [
            'requestMethods',
            'baseUrl',
            'defaultHeaders',
            'prefix',
            'client'
        ];

        foreach ($this as $key => $value) {
            if (!in_array($key, $skip)) {
                if (is_string($this->$key)) {
                    $this->$key = '';
                }

                if (is_array($this->$key)) {
                    $this->$key = [];
                }
            }
        }
    }
}
