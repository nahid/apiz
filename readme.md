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
`app/Services`. Now we are develop a service for https://reqres.in and make a class file `ReqResApiService.php`
which is extend by `\Apiz\AbstractApi` class.

```php
namespace App\Services;

use Apiz\AbstractApi;

class ReqResApiService extends AbstractApi
{
    protected function baseUrl()
    {
        return 'https://reqres.in';
    }
}
```

`baseUrl()` is an abstract method of the `AbstractApi` class. You need to override this method to set the proper base URL for your API.

Few APIs have a common prefix. Like, here `reqres.in` have a prefix 'api' on every endpoint. So, first we set it with protected property `$prefix`.

```php
namespace App\Services;

use Apiz\AbstractApi;

class ReqResApiService extends AbstractApi
{
    protected $prefix = 'api';

    protected function baseUrl()
    {
        return 'https://reqres.in';
    }
}
```

Now let's make a method to get all users info.

```php
namespace App\Services;

use Apiz\AbstractApi;

class ReqResApiService extends AbstractApi
{
    protected $prefix = 'api/';

    protected function baseUrl()
    {
        return 'https://reqres.in';
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
See, how easy it is to manage an API now!

You can easily use all HTTP verbs as a magic method. Its totally hassle free. 

With all of the responses we are returning three objects `response`, `request` and `contents`. We are using `GuzzleHttp` client for this package. That means, you can access all the Guzzle response methods from this response. We are using magic method to access it from the response.

#### Output

![Response](http://imgur.com/IgI0vKb.png?1 "Response")


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

## List of Available methods

- `formParams(array $params)`
- `headers(array $params)`
- `query(array $params)`
- `allowRedirects(array $params)`
- `auth(string $username, string $password [, array $options])`
- `body(string $contents)`
- `json(array $params)`
- `file(string $name, string $file_path, string $filename [, array $headers])`
- `params(array $params)`

## List of methods for common HTTP verbs

- `get(string $uri)`
- `post(string $uri)`
- `put(string $uri)`
- `delete(string $uri)`
- `head(string $uri)`
- `options(string $uri)`

## Extra Methods

- `getGuzzleClient()`
