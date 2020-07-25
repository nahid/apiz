<?php

namespace Examples;

class ExampleAPI extends BaseAPI
{
    public function getAllUsers()
    {
        return $this->get('users');
    }
}
