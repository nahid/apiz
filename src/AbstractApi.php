<?php

namespace Apiz;

use Apiz\Exceptions\UnknownResponseClassException;
use Apiz\Http\AbstractClient;
use Apiz\Http\Clients\GuzzleClient;
use Apiz\Http\Response;

/**
 * Class AbstractApi
 * @package Apiz
 */
abstract class AbstractApi
{
    /**
     * list of available http exceptions
     *
     * @var array
     */
    protected $httpExceptions = [];


    /**
     * skip exception when its value true
     *
     * @var bool
     */
    protected $skipHttpException = false;

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
     * Default Query options for request
     *
     * @var array
     */
    protected $defaultQueries = [];


    /**
     * when need to skip default header make it true
     *
     * @var bool
     */
    protected $skipDefaultHeader = false;

    /**
     * when need to skip default query make it true
     *
     * @var bool
     */
    protected $skipDefaultQueries = false;


    protected $preHookFn = null;

    /**
     * The main client to do all the magic
     *
     * @var AbstractClient
     */
    protected $client;

    /**
     * Request parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * contains custom response
     *
     * @var $response
     */
    protected $response = Response::class;
    /**
     * @var callable
     */
    protected $successHookFn = null;
    /**
     * @var callable
     */
    protected $failsHookFn = null;

    public function __construct()
    {
        $this->baseUrl = $this->baseUrl();

        $this->defaultHeaders = $this->setDefaultHeaders();
        $this->defaultQueries = $this->setDefaultQueries();

        $this->client = $this->getClient();
    }

    protected function getClient()
    {
        return new GuzzleClient();
    }

    /**
     * set base URL for guzzle client
     *
     * @return string
     */
    abstract protected function baseUrl();


    /**
     * set url prefix from code
     * @param string $prefix
     * @return null|string
     */
    protected function setPrefix($prefix = '')
    {
        return $this->prefix = $prefix;
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

    /**
     * set default queries that will automatically bind with every request headers
     *
     * @return array
     */
    protected function setDefaultQueries()
    {
        return [];
    }

    protected function preHook($request)
    {
        return;
    }


    protected function successHook($response, $request)
    {
        return;
    }


    protected function failsHook($exception)
    {
        return;
    }

    public function bindPreHook(callable $fn)
    {
        $this->preHookFn = $fn;
        return $this;
    }

    public function bindSuccessHook(callable $fn)
    {
        $this->successHookFn = $fn;
        return $this;
    }

    public function bindFailsHook(callable $fn)
    {
        $this->failsHookFn = $fn;
        return $this;
    }


    /**
     * @param $uri
     * @return Response
     * @throws \Exception
     */
    public function get($uri)
    {
        return $this->makeMethodRequest('GET', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws \Exception
     */
    public function post($uri)
    {
        return $this->makeMethodRequest('POST', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws \Exception
     */
    public function put($uri)
    {
        return $this->makeMethodRequest('PUT', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws \Exception
     */
    public function patch($uri)
    {
        return $this->makeMethodRequest('PATCH', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws \Exception
     */
    public function delete($uri)
    {
        return $this->makeMethodRequest('DELETE', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws \Exception
     */
    public function head($uri)
    {
        return $this->makeMethodRequest('HEAD', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws \Exception
     */
    public function options($uri)
    {
        return $this->makeMethodRequest('OPTIONS', $uri);
    }


    /**
     * set form parameters or form data for POST, PUT and PATCH request
     *
     * @param array $params
     * @return \Apiz\AbstractApi|bool
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
     * @return \Apiz\AbstractApi|bool
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
     * @return \Apiz\AbstractApi
     */
    protected function skipDefaultHeaders()
    {
        $this->skipDefaultHeader = true;

        return $this;
    }

    /**
     * @return \Apiz\AbstractApi
     */
    protected function skipDefaultQueries()
    {
        $this->skipDefaultQueries = true;

        return $this;
    }


    /**
     * set query parameters
     *
     * @param array $params
     * @return \Apiz\AbstractApi|bool
     */
    protected function params($params = array())
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
     * @return \Apiz\AbstractApi|bool
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
     * @return \Apiz\AbstractApi
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
     * @return \Apiz\AbstractApi|bool
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
     * @return \Apiz\AbstractApi|bool
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
     * @return \Apiz\AbstractApi
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
     * @return \Apiz\AbstractApi
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
     * Attach form value with multipart
     *
     * @param       array $data
     * @return \Apiz\AbstractApi
     */
    protected function formData($data = [])
    {
        foreach($data as $key=>$value) {
            $params = [
                'name' => $key,
                'contents' => $value
            ];

            $this->parameters['multipart'][] = $params;
        }

        return $this;
    }

    /**
     * Set all parameters from this single options
     *
     * @param array $options
     * @return \Apiz\AbstractApi
     */
    protected function parameters($options = [])
    {
        $this->parameters = $options;
        return $this;
    }


    /**
     * skip default http exception from request
     *
     * @param array $exceptions
     * @return \Apiz\AbstractApi
     */
    protected function skipHttpExceptions(array $exceptions = [])
    {
        if (count($exceptions)>0) {
            foreach ($exceptions as $code) {
                unset($this->httpExceptions[$code]);
            }

            return $this;
        }

        $this->skipHttpException = true;

        return $this;
    }

    /**
     * push new http exceptions to current request
     *
     * @param array $exceptions
     * @return \Apiz\AbstractApi
     */
    protected function pushHttpExceptions(array $exceptions = [])
    {
        foreach($exceptions as $code=>$exception) {
            $this->httpExceptions[$code] = $exception;
        }

        return $this;
    }


    /**
     * Make all request from here
     *
     * @param string $method
     * @param string $uri
     * @return Response
     * @throws \Exception
     */
    protected function makeMethodRequest($method, $uri)
    {

        if (!empty($this->prefix)) {
            $this->prefix = trim($this->prefix, '/') . '/';
        }
        $uri = $this->prefix . trim($uri, '/');

        $this->mergeDefaultHeaders();
        $this->mergeDefaultQueries();


        $this->request = [
            'url' => trim($this->baseUrl, '/') . '/' . $uri,
            'method' => $method,
            'parameters' => $this->parameters
        ];

        $url = trim($this->baseUrl, '/') . '/' . $uri;

        $request = $this->client->getRequest($method, $url);

        if (is_null($this->preHookFn)) {
            $this->preHook($request);
        }

        if(is_callable($this->preHookFn)) {
            $preHookFn = $this->preHookFn;
            $preHookFn($request);
        }

        try {
            $response = $this->client->send($request, $this->parameters);

            if (is_null($this->successHookFn)) {
                $this->successHook($response, $request);
            }

            if(is_callable($this->successHookFn)) {
                $successHookFn = $this->successHookFn;
                $successHookFn($response, $request);
            }
        } catch (\Exception $e) {
            if (is_null($this->failsHookFn)) {
                $this->failsHook($e);
            }

            if(is_callable($this->failsHookFn)) {
                $failsHookFn= $this->failsHookFn;
                $failsHookFn($e);
            }
            throw $e;
        }

        if (!$this->skipHttpException) {
            if ($response instanceof \GuzzleHttp\Psr7\Response) {
                new HttpExceptionReceiver($response, $this->httpExceptions);
            }
        }

        $responder = $this->response;

        /**
         * @var $this->response $resp
         */
        $resp = new $responder($response, $request);

        if (is_null($this->response) || !($resp instanceof Response)) {
            throw new UnknownResponseClassException();
        }

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
        $clearGarbage = [
            'skipDefaultHeader' => false,
            'options' => [],
            'request'   => [],
            'parameters' => [],
            'skipHttpException' => false,
        ];

        foreach ($clearGarbage as $key=>$value) {
            if (property_exists($this, $key)) {
                $this->$key=$value;
            }
        }
    }


    protected function mergeDefaultHeaders()
    {
        if (!$this->skipDefaultHeader) {
            if (isset($this->parameters['headers'])) {
                $this->parameters['headers'] = array_merge($this->defaultHeaders, $this->parameters['headers']);
            } else {
                $this->parameters['headers'] = $this->defaultHeaders;
            }

            if (count($this->parameters['headers']) < 1) {
                unset($this->parameters['headers']);
            }
        }
    }

    protected function mergeDefaultQueries()
    {
        if (!$this->skipDefaultQueries) {
            if (isset($this->parameters['query'])) {
                $this->parameters['query'] = array_merge($this->defaultQueries, $this->parameters['query']);
            } else {
                $this->parameters['query'] = $this->defaultQueries;
            }

            if (count($this->parameters['query']) < 1) {
                unset($this->parameters['query']);
            }
        }
    }
}
