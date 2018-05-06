<?php

require 'vendor/autoload.php';

use Apiz\App\ApiManager;


$api = new ApiManager();

$res = $api->albums();

if ($res->isJson()) {
    $r = $res()
        ->from('.')
        ->where('userId', '=', 10)
        ->fetch()
        ->sortAs('id', 'desc')
    ->get();
    dump($r);
}