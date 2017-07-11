<?php

namespace ApiManager\App;

use ApiManager\AbstractApi;

class GithubApi extends AbstractApi
{

    protected $prefix = '/api/v1/';

    protected function setBaseUrl():string
    {
        return "https://domain.com/";
    }


    public function getOrgRepos()
    {
        return $this->post('login');

    }

    public function me()
    {
        return $this->headers([
            'debug'=>true
        ])->get('me');
    }

    protected function getAccessToken(): string
    {
        return '';
    }

    protected function setDefaultHeaders():array
    {
        return ['Authorization'=>'Bearer ' . $this->getAccessToken()];
    }
}
