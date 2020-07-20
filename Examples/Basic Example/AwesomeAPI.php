<?php

namespace Examples;

use Apiz\AbstractApi;

class AwesomeAPI extends AbstractApi
{
    /**
     * @inheritDoc
     */
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
        return $this->get('users')->getContents();
    }

    public function createUser(array $data)
    {
        return $this->json($data)->post('users')->getContents();
    }
}
