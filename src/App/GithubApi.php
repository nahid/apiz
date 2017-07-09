<?php

namespace ApiManager\App;

use ApiManager\AbstractApi;

class GithubApi extends AbstractApi
{
    protected $baseUrl = "https://api.github.com";


    public function getOrgRepos($org)
    {
        return $this->get('/orgs/'. $org .'/repos');

    }

    protected function getAccessToken(): string
    {
        return '';
    }
}
