<?php

require 'vendor/autoload.php';

use Apiz\App\ApiManager;


$api = new ApiManager();

$res = $api->uploads();
//$res = $api->upload();
////$res = $api->createUser(["name"=>"Nahid", "job"=>"Software Engineer"]);


dump($res);