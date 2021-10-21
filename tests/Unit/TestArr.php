<?php

declare(strict_types=1);

namespace Ep\Tests\Unit;

use Ep\Helper\Arr;
use PHPUnit\Framework\TestCase;

class TestArr extends TestCase
{
    /**
     * @dataProvider getValueProvider
     */
    public function testGetValue($array, $key, $default, $except)
    {
        $result = Arr::getValue($array, $key, $default);
        $this->assertSame($except, $result);
    }

    /**
     * @dataProvider getValuesProvider
     */
    public function testGetValues($array, $keys, $default, $except)
    {
        $result = Arr::getValues($array, $keys, $default);
        ksort($result);
        ksort($except);
        $this->assertSame($except, $result);
    }

    public function getValueProvider(): array
    {
        $array = [
            3,
            null,
            '4',
            'id' => 1,
            'address' => [
                'street' => [
                    'door' => 'home'
                ]
            ],
            '1.0' => [
                '2.0' => [
                    '3.0' => 'version'
                ],
                '2.1' => 'last-version'
            ]
        ];
        return [
            [
                'array' => [],
                'key' => null,
                'default' => 'd1',
                'expect' => 'd1'
            ],
            [
                'array' => $array,
                'key' => 1,
                'default' => 'd2',
                'expect' => null
            ],
            [
                'array' => $array,
                'key' => 'address.street',
                'default' => 'd3',
                'expect' => [
                    'door' => 'home'
                ]
            ],
            [
                'array' => $array,
                'key' => 'address.street2',
                'default' => 'd4',
                'expect' => 'd4'
            ],
            [
                'array' => $array,
                'key' => ['1.0', '2.1'],
                'default' => 'd5',
                'expect' => 'last-version'
            ],
            [
                'array' => $array,
                'key' => ['1.0', '2.9', '1.0'],
                'default' => [
                    '1.0' => [
                        '2.9' => 'd6'
                    ]
                ],
                'expect' => [
                    '1.0' => [
                        '2.9' => 'd6'
                    ]
                ]
            ]
        ];
    }

    public function getValuesProvider(): array
    {
        return [
            [
                'array' => [],
                'keys' => ['age', 'name'],
                'default' => ['name' => 'Bob'],
                'expect' => [
                    'age' => null,
                    'name' => 'Bob'
                ]
            ],
            [
                'array' => [
                    'name' => 'Mary',
                    'age' => 11,
                    'address' => 'Haven'
                ],
                'keys' => [],
                'default' => ['name' => 'Bob'],
                'expect' => []
            ],
            [
                'array' => [
                    'name' => 'Mary',
                    'age' => 11,
                    'address' => 'Haven'
                ],
                'keys' => ['name', 'age'],
                'default' => ['name' => 'Bob', 'age' => 33, 'address' => 'Hell'],
                'expect' => [
                    'name' => 'Mary',
                    'age' => 11,
                ]
            ], [
                'array' => [
                    'name' => 'Mary',
                    5 => 'Haven'
                ],
                'keys' => ['name', 'age', 5, 'door'],
                'default' => ['name' => 'Bob', 'age' => 33, 'address' => 'Hell'],
                'expect' => [
                    'name' => 'Mary',
                    'age' => 33,
                    'door' => null,
                    5 => 'Haven',
                ]
            ]
        ];
    }
}
