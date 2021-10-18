<?php

declare(strict_types=1);

use Ep\Tests\Support\ArrService;
use Ep\Tests\Support\CurlService;
use Ep\Tests\Support\DateService;
use Ep\Tests\Support\MathService;
use Ep\Tests\Support\StrService;
use Ep\Tests\Support\UrlService;

require(dirname(__DIR__, 2) . '/vendor/autoload.php');

$str = new StrService();
$arr = new ArrService();
$curl = new CurlService();
$url = new UrlService();
$math = new MathService();
$date = new DateService();

$array = [
    // [$str, 'random'],
    // [$str, 'ltrim'],
    // [$str, 'rtrim'],
    // [$str, 'toPascalCase'],
    // [$str, 'camelToId'],
    // [$arr, 'getValues'],
    // [$arr, 'map'],
    // [$arr, 'merge'],
    // [$date, 'fromUnix'],
    [$curl, 'custom'],
    // [$curl, 'get'],
    // [$curl, 'post'],
    // [$curl, 'getMulti'],
    // [$url, 'addParams'],
    // [$math, 'convertUnit'],
    // [$arr, 'toXml'],
    // [$arr, 'fromXml']
];

$result = array_map(function ($callback) {
    return call_user_func($callback);
}, $array);

print_r($result);
