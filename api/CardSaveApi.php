<?php

namespace Api;

use Apiz\AbstractApi;
use Apiz\Exceptions\NoResponseException;

class CardSaveApi extends AbstractApi
{
    public $prefix = 'storefront/api/v1';
    public $response = NewResponse::class;

    public $httpExceptions = [
        200 => NoResponseException::class
    ];

    public function baseUrl()
    {
        return 'http://deligram.local';
    }

    public function setDefaultHeaders()
    {
        return [
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vZGVsaWdyYW0ubG9jYWwvc3RvcmVmcm9udC9hcGkvdjEvYXV0aC9sb2dpbiIsImlhdCI6MTU2MTUzNDM0MywibmJmIjoxNTYxNTM0MzQzLCJqdGkiOiJKNkR1MWRZUjBDOVlzYTEwIiwic3ViIjo5LCJwcnYiOiIxZDBhMDIwYWNmNWM0YjZjNDk3OTg5ZGYxYWJmMGZiZDRlOGM4ZDYzIiwidHlwZSI6IkFwcFxcTW9kZWxzXFxDdXN0b21lciJ9.H3dvGn1RRrIwt2pdAJnOS5PEZusFYOx7-HsUl1poqLU',
        ];
    }

    public function save($data)
    {
        return $this->skipHttpExceptions([200])->json($data)->post('cart/save');
    }
}