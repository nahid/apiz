<?php

namespace ApiManager\App;

use ApiManager\AbstractApi;

class GithubApi extends AbstractApi
{

    protected $prefix = '/api/v1/';

    protected function setBaseUrl():string
    {
        return "https://area51.pathao.com/";
    }


    public function getOrgRepos()
    {
        return $this->formParams([
            'client_id'=>'60b700b76c44ef656f21ebffb07205c1',
            'client_secret'=>'5c8a5a7802d8814452537203785166c7',
            'grant_type'=>'password',
            'username'=>'01882123456@pathao.com',
            'password'=>'1234',
            'scope'=>'crud_me'
        ])->post('login');

    }

    public function me()
    {
        return $this->headers([
            'debug'=>true
        ])->get('me');
    }

    protected function getAccessToken(): string
    {
        return '4CaYHnlQCCaMPLSvzYQyZXO7KP0X3Bfh0Sn4uEWW';
    }

    protected function setDefaultHeaders():array
    {
        return ['Authorization'=>'Bearer ' . $this->getAccessToken()];
    }
}
