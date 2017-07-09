<?php

require 'vendor/autoload.php';

use ApiManager\App\GithubApi;

$api = new GithubApi();

$res = $api->getOrgRepos('codesum');

echo '<pre><code>';
var_dump($res->getData());