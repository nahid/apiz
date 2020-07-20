<?php

namespace Examples;

use Apiz\AbstractApi;

class AwesomeAPI extends AbstractApi
{
    public function __construct()
    {
        parent::__construct();

        $this->setBaseURL('https://reqres.in');
        $this->setPrefix('api');
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
