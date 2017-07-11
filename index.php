<?php

require 'vendor/autoload.php';

use ApiManager\App\GithubApi;

$api = new GithubApi();

$res = $api->me();


dump($res);