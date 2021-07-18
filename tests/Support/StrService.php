<?php

declare(strict_types=1);

namespace Ep\Tests\Support;

use Ep\Helper\Str;

class StrService
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

    public function ltrim()
    {
        return [
            Str::ltrim('getAction', 'get'),
            Str::ltrim('abc/doAction', 'c/do'),
            Str::ltrim('abc/doAction', 'c/do', false),
        ];
    }

    public function rtrim()
    {
        return Str::rtrim('abc/doAction', 'bc/doAction');
    }

    public function toPascalCase()
    {
        $input = 'ab/c-d-e/fgh';
        return Str::toPascalCase($input);
    }

    public function camelToId()
    {
        $input1 = 'Admin12$Back3End4CCC5#TT6e7ID8Command9/10SAY';
        $r1 = Str::camelToId($input1, '-', true);
        $input2 = $input1;
        $r2 = Str::camelToId($input2, '-', false);
        return [$r1, $r2];
    }
}
