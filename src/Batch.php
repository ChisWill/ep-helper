<?php

declare(strict_types=1);

namespace Ep\Helper;

class Batch
{
    public static function reduce(callable $producer, callable ...$callbacks): array
    {
        $result = [];
        while (true) {
            $data = $producer();
            if (!$data) {
                break;
            }
            foreach ($callbacks as $callback) {
                $data = $callback($data);
            }
            $result[] = $data;
        }
        return $result;
    }
}
