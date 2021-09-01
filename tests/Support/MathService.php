<?php

declare(strict_types=1);

namespace Ep\Tests\Support;

use Ep\Helper\Math;

class MathService
{
    public function convertUnit()
    {
        $number = -3333.0;
        return Math::convertUnit($number, ['B', 'KB', 'MB'], 1024);
    }
}
