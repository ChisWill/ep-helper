<?php

declare(strict_types=1);

use Ep\Tests\Support\TestArr;
use Ep\Tests\Support\TestCurl;
use Ep\Tests\Support\TestStr;

require(dirname(__DIR__) . '/vendor/autoload.php');

$str = new TestStr();
$arr = new TestArr();
$curl = new TestCurl();

$s1 = $str->random();
$a1 = $arr->getValues();
$a2 = $arr->map();
$c1 = $curl->get();

test(
    $a2
);
