<?php

require 'vendor/autoload.php';

use ApiManager\App\ReqResApiService;

$api = new ReqResApiService();

$res = $api->allUsers();
//$res = $api->createUser(["name"=>"Nahid", "job"=>"Software Engineer"]);


dump($res);