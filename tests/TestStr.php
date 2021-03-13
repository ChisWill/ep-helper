<?php

declare(strict_types=1);

namespace Ep\Tests;

use Ep\Helper\Str;

class TestStr
{
    public function sign()
    {
        return Str::getSign([
            'a' => 252342,
            'b' => 'a~!@#$123',
            'cd' => false,
            'ef' => true
        ], 'secret-key');
    }
}
