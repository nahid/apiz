<?php

namespace Apiz;

use Apiz\Http\AbstractClient;
use Apiz\Http\Clients\GuzzleClient;
use Apiz\Http\Request;
use Apiz\Http\Response;
use Apiz\Traits\Hookable;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Apiz\Exceptions\InvalidResponseClassException;

/**
 * Class AbstractApi
 * @package Apiz
 */
abstract class AbstractApi
{
    use Hookable;

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
    protected $shouldSkipHttpException = false;

    /**
     * this variable contains request details
     *
     * @var Request
     */
    protected $request;

    /**
     * response class name
     *
     * @var string
     */
    protected $response = Response::class;

    /**
     * AbstractApi constructor.
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        if (!$request) {
            $this->request = new Request(new GuzzleClient());
        }

        $this->setBaseURL($this->getBaseURL());
        $this->setPrefix($this->getPrefix());
    }

    /**
     * @return string
     */
    abstract protected function getBaseURL();

    /**
     * set Base URL
     *
     * @param string $url
     */
    private function setBaseURL($url)
    {
        $this->request->setBaseURL($url);
    }

    /**
     * @return string
     */
    protected function getPrefix()
    {
        return '';
    }

    /**
     * set url prefix
     *
     * @param string $prefix
     */
    private function setPrefix($prefix)
    {
        $this->request->setPrefix($prefix);
    }

    /**
     * @param AbstractClient $client
     */
    protected function setClient(AbstractClient $client)
    {
        $this->request->setClient($client);
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return Response
     * @throws InvalidResponseClassException
     */
    private function makeResponse(Request $request, ResponseInterface $response)
    {
        $responseClass = $this->response;
        $apizResponse = new $responseClass($request, $response);

        if (!($apizResponse instanceof Response)) {
            throw new InvalidResponseClassException();
        }

        return $apizResponse;
    }

    /**
     * @param string $responseClass
     */
    protected function setResponseClass($responseClass)
    {
        $this->response = $responseClass;
    }

    /**
     * set form parameters or form data for POST, PUT and PATCH request
     *
     * @param array $params
     * @return AbstractApi
     */
    protected function withFormParams(array $params = [])
    {
        $this->request->setParameters($params, 'form_params');

        return $this;
    }

    /**
     * set request headers
     *
     * @param array $params
     * @return AbstractApi|bool
     */
    protected function withHeaders(array $params = [])
    {
        $this->request->setParameters($params, 'headers');

        return $this;
    }

    /**
     * get default headers that will automatically bind with every request headers
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return [];
    }

    /**
     * get default queries that will automatically bind with every request
     *
     * @return array
     */
    protected function getDefaultQueries()
    {
        return [];
    }

    /**
     * @param bool $action
     * @return self
     */
    protected function skipDefaultHeaders($action = true)
    {
        $this->request->skipDefaultHeaders($action);

        return $this;
    }

    /**
     * @param bool $action
     * @return self
     */
    protected function skipDefaultQueries($action = true)
    {
        $this->request->skipDefaultQueries($action);

        return $this;
    }


    /**
     * set query parameters
     *
     * @param array $params
     * @return AbstractApi|bool
     */
    protected function withQueryParams(array $params = [])
    {
        $this->request->setParameters($params, 'query');

        return $this;
    }

    /**
     * Add allow redirects param
     *
     * @param array $params
     * @return AbstractApi|bool
     */
    protected function allowRedirects(array $params = [])
    {
        $this->request->setParameters($params, 'allow_redirects');

        return $this;
    }

    /**
     * Set basic auth options
     *
     * @param string $username
     * @param string $password
     * @param array $opts
     * @return AbstractApi
     */
    protected function basicAuth($username, $password, array $opts = [])
    {
        $params = [$username, $password];

        if (!empty($opts)) {
            $params = array_merge($params, $opts);
        }

        $this->request->setParameters($params, 'auth');

        return $this;
    }

    /**
     * Set request body
     *
     * @param string|array $contents
     * @return AbstractApi|bool
     */
    protected function body($contents)
    {
        if (is_array($contents)) {
            $this->withHeaders([
                'Content-Type'=>'application/json'
            ]);

            $contents = json_encode($contents);
        }

        $this->request->setParameters($contents, 'body');

        return $this;
    }

    /**
     * Set request param as JSON
     *
     * @param array $params
     * @return AbstractApi|bool
     */
    protected function json(array $params = [])
    {
        $this->request->setParameters($params, 'json');

        return $this;
    }

    /**
     * Send file to the request
     *
     * @param string $name
     * @param string $file
     * @param string $filename
     * @param array $headers
     * @return AbstractApi
     */
    protected function file($name, $file, $filename, array $headers = [])
    {
        if (file_exists($file)) {
            $contents = fopen($file, 'r');

            return $this->attach($name, $contents, $filename, $headers);
        }

        return $this;
    }

    /**
     * Attach a raw content with request
     *
     * @param string $name
     * @param string $contents
     * @param string $filename
     * @param array $headers
     * @return AbstractApi
     */
    protected function attach($name, $contents, $filename, array $headers = [])
    {
        $this->request->setParameters([
            'name' => $name,
            'contents' => $contents,
            'filename' => $filename,
            'headers' => $headers
        ], 'multipart');

        return $this;
    }

    /**
     * Attach form value with multipart
     *
     * @param array $data
     * @return AbstractApi
     */
    protected function withFormData(array $data = [])
    {
        $params = [];
        foreach($data as $key => $value) {
            $params[] = [
                'name' => $key,
                'contents' => $value
            ];
        }

        if (!empty($params)) {
            $this->request->setParameters($params, 'multipart');
        }

        return $this;
    }

    /**
     * skip default http exceptions from request
     *
     * @param array $codes
     * @return AbstractApi
     */
    protected function skipHttpExceptions(array $codes = [])
    {
        if (!empty($codes)) {
            $this->shouldSkipHttpException = true;

            foreach ($codes as $code) {
                unset($this->httpExceptions[$code]);
            }
        }

        return $this;
    }

    /**
     * push new http exceptions to current request
     *
     * @param array $exceptions
     * @return AbstractApi
     */
    protected function pushHttpExceptions(array $exceptions = [])
    {
        foreach($exceptions as $code => $exception) {
            $this->httpExceptions[$code] = $exception;
        }

        return $this;
    }

    /**
     * @param $uri
     * @return Response
     * @throws Exception
     */
    protected function get($uri)
    {
        return $this->makeMethodRequest('GET', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws Exception
     */
    protected function post($uri)
    {
        return $this->makeMethodRequest('POST', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws Exception
     */
    protected function put($uri)
    {
        return $this->makeMethodRequest('PUT', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws Exception
     */
    protected function patch($uri)
    {
        return $this->makeMethodRequest('PATCH', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws Exception
     */
    protected function delete($uri)
    {
        return $this->makeMethodRequest('DELETE', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws Exception
     */
    protected function head($uri)
    {
        return $this->makeMethodRequest('HEAD', $uri);
    }

    /**
     * @param $uri
     * @return Response
     * @throws Exception
     */
    protected function options($uri)
    {
        return $this->makeMethodRequest('OPTIONS', $uri);
    }

    /**
     * Make all request from here
     *
     * @param string $method
     * @param string $uri
     * @return Response
     * @throws Exception
     */
    private function makeMethodRequest($method, $uri)
    {
        $this->executePreHooks($this->request);

        $response = null;
        try {
            $this->request->setDefaultHeaders($this->getDefaultHeaders());
            $this->request->setDefaultQueries($this->getDefaultQueries());

            $clientResponse = $this->request->send($method, $uri);
            $response = $this->makeResponse($this->request, $clientResponse);

            if (!$this->shouldSkipHttpException) {
                if ($response instanceof Response) {
                    new HttpExceptionReceiver($response, $this->httpExceptions);
                }
            }

            $this->executeSuccessHooks($clientResponse, $this->request);
        } catch (Exception $e) {
            $this->executeFailHooks($e);
            throw $e;
        } finally {
            $this->resetObjects();
        }

        return $response;
    }

    /**
     * Reset this class objects
     */
    protected function resetObjects()
    {
        $this->shouldSkipHttpException = false;
        $this->request = null;
    }
}
