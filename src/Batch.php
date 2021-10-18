<?php

declare(strict_types=1);

namespace Ep\Helper;

final class Batch
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
                $data = call_user_func($callback, $data);
            }
            $result[] = $data;
        }
        return $result;
    }
}
