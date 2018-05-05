<?php

require 'vendor/autoload.php';

use Apiz\App\ReqResApiService;


$api = new ReqResApiService();

$res = $api->allUsers();

if ($res->isJson()) {
    $r = $res->json()
        ->from('data')
        ->where('year', '>=', 2001)
        ->fetch()
        ->sortAs('year', 'asc')
        ->get();
    dump($r);
}