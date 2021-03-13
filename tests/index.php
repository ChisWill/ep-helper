<?php

declare(strict_types=1);

use Ep\Tests\TestStr;

require(dirname(__DIR__) . '/vendor/autoload.php');

$case = new TestStr();
test($case->sign());
