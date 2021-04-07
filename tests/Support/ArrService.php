<?php

declare(strict_types=1);

namespace Ep\Tests\Support;

use Ep\Helper\Arr;

class ArrService
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

    private array $a1 = [
        'a' => 1,
        'b' => [1, 2],
        'c' => []
    ];

    private array $a2 = [
        'a' => 12,
        'b' => [1, 2, 3],
        'c' => [4]
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

    public function merge()
    {
        return Arr::merge($this->a1, $this->a2);
    }
}
