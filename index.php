<?php

require 'vendor/autoload.php';

use Apiz\App\ApiManager;


$api = new ApiManager();

$res = $api->getUsers();
//$res = $api->upload();
////$res = $api->createUser(["name"=>"Nahid", "job"=>"Software Engineer"]);


dump($res->getMimeType());