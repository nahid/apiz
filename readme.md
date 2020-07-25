# APIZ

APIZ is a PHP API Client Development Kit. You can easily handle all kinds of JSON API responses using this package.

## Requirements

- PHP >= 5.5.9

## Installations

```shell
composer require nahid/apiz
```

## Configurations

There are no extra configurations for this package.

## Usage

Lets make an API service for https://reqres.in.

Suppose you have to make several api service for your package. Your service directory is
`app/Services`. Now we are going to develop a service for https://reqres.in and make a class file `ReqResApiService.php`
which is extend by `\Apiz\AbstractApi` class.

```php
namespace App\Services;

use Apiz\AbstractApi;

class ReqResApi extends AbstractApi
{
    protected function getBaseURL()
    {
        return 'https://reqres.in';
    }
}
```

`getBaseURL()` is an abstract method of the `AbstractApi` class. You need to override this method to set the proper base URL for your API.

Few APIs have a common prefix in their URL. Like, here `reqres.in` have a prefix `api` on every endpoint. 
So, we'll override the `getPrefix()` method to define the Prefix.

```php
namespace App\Services;

use Apiz\AbstractApi;

class ReqResApiService extends AbstractApi
{
    protected function getBaseURL()
    {
        return 'https://reqres.in';
    }

    protected function getPrefix()
    {
        return 'api';
    }
}
```

Now let's make a method to get all users info.

```php
namespace App\Services;

use Apiz\AbstractApi;

class ReqResApiService extends AbstractApi
{
    protected function getBaseURL()
    {
        return 'https://reqres.in';
    }

    protected function getPrefix()
    {
        return 'api';
    }

    public function getAllUsers()
    {
        $response = $this->get('users');

        if ($response->getStatusCode() === 200) {
            return $response;
        }

        return [];
    }
}
```
See, how easy it is to manage an API now?

Let's see another example.

## Post Request with Form Params

```php
public function createUser(array $data)
{
    $response = $this->formParams($data)
            ->post('create');

    if ($response->getStatusCode() === 201) {
        return $response;
    }

    return null;
}
```

You can easily use all HTTP verbs like `get`, `post` etc. Its totally hassle free. 
See more examples in the [Examples Folder](./Examples).

## Query over Response Data

Sometimes we receive huge payload as a response from the APIs. 
It's quite daunting to parse proper data from that big payload.

No worries!
We're using a powerful Query parser, named [QArray](https://github.com/nahid/qarray) by default to parse and query over the Response data.

Let's see how we can use this parser to parse the response we got for `getAllUsers` method from our previous example.

```php
public function getFirstUser()
{
    $users = $this->get('users');
    return $users->query()->from('data')->first();
}
```

We're getting list of users in the `data` key in the response. From that we're collecting the first data.
See, how easy it is!

You can find detailed usage of the QArray [here](https://github.com/nahid/qarray).

Additionally, there is a secret sauce for you. 

If you don't want to query like: `$users->query()`, you can just do it like this: `$users()`. That means the response object is invokable and behave exactly like calling the `query()` method.
You're welcome. :D 

## Overriding HTTP Client

By default we are using `Guzzle` as our HTTP Client. But you are not bound to use this. You can easily use your own PSR7 supported HTTP Client with `Apiz`.
Just pass an instance of your HTTP Client to our `setClient()` method.
See an example [here](./Examples/API%20with%20Different%20Client).

Here is our GuzzleClient to get an idea how your Client should look like:
```php
<?php

namespace Apiz\Http\Clients;

use Apiz\Http\AbstractClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

// Your client must extend the `AbstractClient`
class GuzzleClient extends AbstractClient
{
    public function getRequestClass()
    {
        // Return the Request class name of your PSR7 supported Client
        // This Request class must implement the Psr\Http\Message\RequestInterface
        return Request::class;
    }

    public function getResponseClass()
    {
        // Return the Response class name of your PSR7 supported Client
        // This Response class must implement the Psr\Http\Message\ResponseInterface        
        return Response::class;
    }

    public function getUriClass()
    {
        // Return the Uri class name of your PSR7 supported Client
        // This Uri class must implement the Psr\Http\Message\UriInterface        
        return Uri::class;
    }

    /**
     * @param mixed ...$args
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function send(...$args)
    {
        // In this method, implement how your Client execute the Request sending
        $client = new Client();

        return $client->send(... $args);
    }
}
```

## List of methods for common HTTP verbs

- `get(string $uri)`
- `post(string $uri)`
- `put(string $uri)`
- `delete(string $uri)`
- `head(string $uri)`
- `options(string $uri)`

## List of Available methods

- `getPrefix():string`: override this method to define the common prefix, if you need it
- `setClient($client)` : pass a PSR7 supported Client instance, only if you need to override the default Guzzle HTTP Client
- `withFormParams(array)`: pass Form parameters data for requests like POST, PATCH, UPDATE
- `withHeaders(array)`: pass Header data
- `withQueryParams(array)`: pass Query Parameter data
- `withFormData(array)`: pass Multipart form data
- `getDefaultHeaders():array`: override to define default Headers, if you have any
- `getDefaultQueries():array`: override to define default queries, if you have any
- `skipDefaultHeaders(bool)`
- `skipDefaultQueries(bool)`
- `allowRedirects(array)`
- `basicAuth(string $username, string $password [, array $options])`
- `body(string)`: Set request body
- `json(array)`: Set JSON data to be passed as Request Body
- `file(string $name, string $file_path, string $filename [, array $headers])`
- `params(array $params)`
