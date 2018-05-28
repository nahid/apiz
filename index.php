<?php

require 'vendor/autoload.php';

use Apiz\App\Mockery;


$api = new Mockery();

$res = $api->products();

if ($res->isJson()) {
    $r = $res()->whereMonth('createdAt', '05')->get();
    dump($r);
}