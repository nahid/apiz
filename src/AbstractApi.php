<?php

declare(strict_types=1);

namespace Apiz;

use Apiz\GraphQL\AbstractRequest;
use Apiz\Http\Clients\AbstractClient;
use Apiz\Http\Clients\GuzzleClient;
use Apiz\Http\Request;
use Apiz\Http\Response;
use Apiz\Traits\Hookable;
use Exception;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Apiz\Exceptions\InvalidResponseClassException;

/**
 * Class AbstractApi
 * @template Resp
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
    protected array $httpExceptions = [];

    /**
     * skip exception when its value true
     *
     * @var bool
     */
    protected bool $shouldSkipHttpException = false;

    /**
     * this variable contains request details
     *
     * @var Request
     */
    protected $request;

    /**
     * response class name
     * @var class-string<Resp> $response
     */
    protected $response = Response::class;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * AbstractApi constructor.
     * @param ?Request $request
     */
    public function __construct($request = null)
    {
        if (!$request || !$request->hasClient()) {
            $this->request = new Request(new GuzzleClient($this->config));
        }

        $this->setBaseURL($this->getBaseURL());
        $this->setPrefix($this->getPrefix());
    }

    /**
     * @return string
     */
    abstract protected function getBaseURL(): string;

    /**
     * Get client configs
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set client config
     *
     * @param $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Get request instance
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
    /**
     * set Base URL
     *
     * @param string $url
     */
    protected function setBaseURL(string $url)
    {
        $this->request->setBaseURL($url);
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return '';
    }

    /**
     * set url prefix
     *
     * @param string $prefix
     */
    protected function setPrefix(string $prefix)
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
     * @return Resp
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
        $this->request->setContentType('application/x-www-form-urlencoded');
        $body = urlencode(http_build_query($params));
        $this->request->setBodyContents($body);

        return $this;
    }

    /**
     * set request headers
     *
     * @param array $headers
     * @return AbstractApi
     */
    protected function withHeaders(array $headers = []): self
    {
        $this->request->setHeaders($headers);

        return $this;
    }

    /**
     * get default headers that will automatically bind with every request headers
     *
     * @return array
     */
    protected function getDefaultHeaders(): array
    {
        return [];
    }

    /**
     * get default queries that will automatically bind with every request
     *
     * @return array
     */
    protected function getDefaultQueries(): array
    {
        return [];
    }

    /**
     * @param bool $action
     * @return self
     */
    protected function skipDefaultHeaders(bool $action = true): self
    {
        $this->request->skipDefaultHeaders($action);

        return $this;
    }

    /**
     * @param bool $action
     * @return self
     */
    protected function skipDefaultQueries(bool $action = true): self
    {
        $this->request->skipDefaultQueries($action);

        return $this;
    }


    /**
     * set query parameters
     *
     * @param array $params
     * @return AbstractApi
     */
    protected function withQueryParams(array $params = []): self
    {
        $this->request->setQueryParams($params);

        return $this;
    }

    protected function withOptions(array $options = []): self
    {
        $this->request->setOptions($options);

        return $this;
    }

    /**
     * Add allow redirects param
     *
     * @param array|null $option
     * @return AbstractApi
     */
    protected function allowRedirects(?array $option = []): self
    {
        if (empty($option)) {
            $option = true;
        }

        $this->request->setOption(RequestOptions::ALLOW_REDIRECTS, $option);

        return $this;
    }

    /**
     * Set basic auth options
     *
     * @param string $username
     * @param string $password
     * @return AbstractApi
     */
    protected function basicAuth(string $username, string $password): self
    {
        $this->request->setHeader('Authorization', 'Basic ' . base64_encode("{$username}:{$password}"));

        return $this;
    }

    /**
     * Set request body
     *
     * @param mixed $contents
     * @return AbstractApi
     */
    protected function withBody($contents): self
    {
        if (is_array($contents)) {
            $this->request->setContentType('x-www-form-urlencoded');

            $contents = urlencode(http_build_query($contents));
        }

        $this->request->setBodyContents($contents);

        return $this;
    }

    /**
     * Set request param as JSON
     *
     * @param array $params
     * @return AbstractApi
     */
    protected function withJson(array $params = []): self
    {
        $this->request->setHeader('Content-Type', 'application/json');
        $this->request->setBodyContents(json_encode($params));

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
    protected function withFile(string $name, string $file, string $filename, array $headers = []): self
    {
        if (file_exists($file)) {
            $contents = Utils::tryFopen($file, 'r');

            return $this->attach($name, $contents, $filename, $headers);
        }

        return $this;
    }

    /**
     * Attach a raw content with request
     *
     * @param string $name
     * @param mixed $contents
     * @param string $filename
     * @param array $headers
     * @return self
     */
    protected function attach(string $name, $contents, string $filename, array $headers = []): self
    {
        $this->request->setBodyMultipart([
            'name' => $name,
            'contents' => $contents,
            'filename' => $filename,
            'headers' => $headers
        ]);

        return $this;
    }

    /**
     * Attach form value with multipart
     *
     * @param array $data
     * @return AbstractApi
     */
    protected function withFormData(array $data = []): self
    {
        $params = $this->prepareFormData($data);

        if (!empty($params)) {
            $this->request->setBodyParams($params);
        }

        return $this;
    }

    /**
     * @param array $params
     * @param string $prefix
     * @return array
     */
    protected function prepareFormData(array $params, string $prefix = ''): array
    {
        $formParams = [];

        foreach ($params as $key => $value) {
            $newKey = empty($prefix) ? $key : $prefix . '[' . $key . ']';
            if (is_array($value)) {
                $formParams = array_merge($formParams, $this->prepareFormData($value, $newKey));
            } else {
                $formParams[] = [
                    'name' => $newKey,
                    'contents' => $value,
                ];
            }
        }
        return $formParams;
    }

    /**
     * skip default http exceptions from request
     *
     * @param array $codes
     * @return AbstractApi
     */
    protected function skipHttpExceptions(array $codes = []): self
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
    protected function pushHttpExceptions(array $exceptions = []): self
    {
        foreach($exceptions as $code => $exception) {
            $this->httpExceptions[$code] = $exception;
        }

        return $this;
    }

    /**
     * @param string $uri
     * @return Resp
     * @throws Exception
     */
    protected function get(string $uri)
    {
        return $this->makeMethodRequest('GET', $uri);
    }

    /**
     * @param string $uri
     * @return Resp
     * @throws Exception
     */
    protected function post(string $uri)
    {
        return $this->makeMethodRequest('POST', $uri);
    }

    /**
     * @param string $uri
     * @return Resp
     * @throws Exception
     */
    protected function put(string $uri)
    {
        return $this->makeMethodRequest('PUT', $uri);
    }

    /**
     * @param string $uri
     * @return Resp
     * @throws Exception
     */
    protected function patch(string $uri)
    {
        return $this->makeMethodRequest('PATCH', $uri);
    }

    /**
     * @param string $uri
     * @return Resp
     * @throws Exception
     */
    protected function delete(string $uri)
    {
        return $this->makeMethodRequest('DELETE', $uri);
    }

    /**
     * @param string $uri
     * @return Resp
     * @throws Exception
     */
    protected function head(string $uri)
    {
        return $this->makeMethodRequest('HEAD', $uri);
    }

    /**
     * @param string $uri
     * @return Resp
     * @throws Exception
     */
    protected function options(string $uri)
    {
        return $this->makeMethodRequest('OPTIONS', $uri);
    }


    protected function graphqlCall(AbstractRequest $request)
    {
        return $this->graphql('', $request);
    }

    protected function graphql(string $uri, AbstractRequest $request)
    {
        return $this
            ->withJson($request->getQuery())
            ->post($uri);
    }

    /**
     * Make all request from here
     *
     * @param string $method
     * @param string $uri
     * @return Resp
     * @throws Exception
     */
    private function makeMethodRequest(string $method, string $uri)
    {
        $response = null;
        try {
            $this->request->setDefaultHeaders($this->getDefaultHeaders());
            $this->request->setDefaultQueries($this->getDefaultQueries());

            $request = $this->request->make($method, $uri);
            $this->executePreHooks($this->request);
            $clientResponse = $this->request->send($request);
            $response = $this->makeResponse($this->request, $clientResponse);

            if (!$this->shouldSkipHttpException) {
                if ($response instanceof Response) {
                    new HttpExceptionReceiver($response, $this->httpExceptions);
                }
            }

            $this->executeSuccessHooks($response, $this->request);
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
        $this->request->reset();
    }
}
