<?php

declare(strict_types=1);

namespace Ep\Tests\Support;

use Ep\Helper\Curl;

class TestCurl
{
    public function get()
    {
        return Curl::get('http://ep.cc/demo/request');
    }
}
