<?php

namespace Tests\Feature\Mocks;

use Apiz\AbstractApi;

class BasicHTTPAPI extends AbstractApi
{
    protected function getBaseURL()
    {
        return 'https://reqres.in';
    }

    public function getPrefix()
    {
        return 'api';
    }

    public function getAllUsers()
    {
        return $this->get('users');
    }

    public function createUser(array $data)
    {
        return $this->withJson($data)->post('users');
    }

    public function updateUser(array $data)
    {
        return $this->withJson($data)->put('users');
    }

    public function partiallyUpdateUser(array $data)
    {
        return $this->withJson($data)->patch('users');
    }

    public function deleteUser($id)
    {
        return $this->delete("users/{$id}");
    }
}
