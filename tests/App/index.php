<?php

declare(strict_types=1);

use Ep\Tests\Support\ArrService;
use Ep\Tests\Support\CurlService;
use Ep\Tests\Support\StrService;
use Ep\Tests\Support\UrlService;

require(dirname(__DIR__, 2) . '/vendor/autoload.php');

$str = new StrService();
$arr = new ArrService();
$curl = new CurlService();
$url = new UrlService();

$s1 = $str->random();
$s2 = $str->rtrim();
$s3 = $str->toPascalCase();
$s4 = $str->camelToId();
$a1 = $arr->getValues();
$a2 = $arr->map();
$a3 = $arr->merge();
$c1 = $curl->get();
$u1 = $url->addParams();

tt(
    $s3,
    $s4,
);
