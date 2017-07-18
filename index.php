<?php

require 'vendor/autoload.php';

use ApiManager\App\ReqResApiService;

$api = new ReqResApiService();

$res = $api->allUsers();


dump($res);