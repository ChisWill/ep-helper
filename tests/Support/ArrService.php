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

    public function getValue()
    {
        $user = [
            'id' => 1,
            'address' => [
                'street' => [
                    'door' => 'home'
                ]
            ],
            '1.0' => [
                '2.0' => [
                    '3.0' => 'version'
                ]
            ]
        ];
        $id = Arr::getValue($user, 'id', 'defaultValue');
        $street = Arr::getValue($user, 'address.street.door', 'defaultValue');
        $version = Arr::getValue($user, ['1.0', '2.01', '3.0'], [
            '2.01' => [
                '3.0' => 'version'
            ]
        ]);
        return [$id, $street, $version];
    }

    public function getValues()
    {
        $arr = ['a' => 123, 'b' => 'abc'];
        $keys = ['a', 'c'];

        return Arr::getValues($arr, $keys, []);
    }

    public function removeKeys()
    {
        $arr = ['a' => 123, 'b' => 'abc'];
        $keys = ['a', 'b', 'c'];

        return [Arr::removeKeys($arr, $keys), $arr];
    }

    public function map()
    {
        return Arr::map($this->data, 'id1', 'nam2e', '3age');
    }

    public function merge()
    {
        return Arr::merge($this->a1, $this->a2);
    }

    public function toXml()
    {
        $data = [
            'name' => 'zhagnsan',
            'age' => 13,
            'email' => ''
        ];

        return Arr::toXml($data);
    }

    public function fromXml()
    {
        $xml = <<<XML
<xml><name><![CDATA[zhagnsan]]></name><age>13</age><email><![CDATA[]]></email></xml>
XML;
        return Arr::fromXml($xml);
    }
}
