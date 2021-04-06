<?php

declare(strict_types=1);

namespace Ep\Tests\Support;

use Ep\Helper\Arr;

class TestArr
{
    private array $data = [
        [
            'id' => 1,
            'name' => 'Peter',
            'age' => 20
        ],
        [
            'id' => 2,
            'name' => 'Mary',
            'age' => 30
        ],
        [
            'id' => 3,
            'name' => 'Bob',
            'age' => 30
        ]
    ];

    public function getValues()
    {
        $arr = ['a' => 123, 'b' => 'abc'];
        $keys = ['af', 'c'];

        return Arr::getValues($arr, $keys, ['lala']);
    }

    public function map()
    {
        return Arr::map($this->data, 'id1', 'nam2e', '3age');
    }
}
