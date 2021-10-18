<?php

declare(strict_types=1);

namespace Ep\Tests\Support;

use Ep\Helper\Date;

class DateService
{
    public function fromUnix()
    {
        return Date::fromUnix();
    }
}
