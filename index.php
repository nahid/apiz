<?php

require 'vendor/autoload.php';

use Apiz\App\ReqResApiService;

$api = new ReqResApiService();

$res = $api->allUsers();
//$res = $api->createUser(["name"=>"Nahid", "job"=>"Software Engineer"]);


dump($res);