<?php

declare(strict_types=1);

use Ep\Tests\Support\ArrService;
use Ep\Tests\Support\CurlService;
use Ep\Tests\Support\MathService;
use Ep\Tests\Support\StrService;
use Ep\Tests\Support\UrlService;

require(dirname(__DIR__, 2) . '/vendor/autoload.php');

$str = new StrService();
$arr = new ArrService();
$curl = new CurlService();
$url = new UrlService();
$math = new MathService();

$array = [
    // [$str, 'random'],
    // [$str, 'ltrim'],
    // [$str, 'rtrim'],
    // [$str, 'toPascalCase'],
    // [$str, 'camelToId'],
    // [$arr, 'getValues'],
    // [$arr, 'map'],
    // [$arr, 'merge'],
    // [$curl, 'get'],
    // [$url, 'addParams'],
    // [$math, 'convertUnit'],
    [$arr, 'fromXml']
];

$result = array_map(function ($callback) {
    return call_user_func($callback);
}, $array);

print_r($result);
