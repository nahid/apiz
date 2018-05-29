<?php

require 'vendor/autoload.php';

use Apiz\App\HttpBin;


$api = new HttpBin();

$res = $api->xml();

if ($res->getStatusCode() == 200) {
    dump($res->autoParse());
}

if ($res->getStatusCode() == 401) {
    echo 'Unauthorized';
    dump($res->size());
}