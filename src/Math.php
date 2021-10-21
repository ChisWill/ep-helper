<?php

declare(strict_types=1);

namespace Ep\Helper;

final class Math
{
    /**
     * 单位转换
     * 
     * Usage:
     * 
     * ```php
     * Math::convertUnit(3333, ['B', 'KB', 'MB'], 1024); // 3.25KB
     * ```
     * 
     * @param  int|float $number
     * 
     * @return string
     */
    public static function convertUnit($number, array $units, int $base = 10, int $precision = 2): string
    {
        if ($number == 0) {
            return 0 . ($units[0] ?? '');
        }
        return round($number / pow($base, ($i = floor(log(abs($number), $base)))), $precision) . ($units[$i] ?? '');
    }

    /**
     * 判断是否是质数
     * 
     * @param  int $number
     * 
     * @return bool
     */
    public static function isPrime(int $number): bool
    {
        if ($number < 2) {
            return false;
        }
        if ($number == 2 || $number == 3) {
            return true;
        }
        if ($number % 6 != 1 && $number % 6 != 5) {
            return false;
        }
        $sqrt = sqrt($number);
        for ($i = 5; $i <= $sqrt; $i += 6) {
            if ($number % $i == 0 || $number % ($i + 2) == 0) {
                return false;
            }
        }
        return true;
    }
}
