<?php

namespace Examples;

class ExampleAPI extends BaseAPI
{
    public function getAllUsers()
    {
        return $this->get('users')->getContents();
    }

    public function createUser(array $data)
    {
        return $this->json($data)->post('users')->getContents();
    }
}
