<?php

declare(strict_types=1);

namespace Ep\Tests\Support;

use Ep\Helper\Str;

class TestStr
{
    public function getSign()
    {
        return Str::getSign([
            'a' => 252342,
            'b' => 'a~!@#$123',
            'cd' => false,
            'ef' => true
        ], 'secret-key');
    }

    public function random()
    {
        return Str::random(12, ['A', 'a']);
    }
}
