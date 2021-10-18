<?php

declare(strict_types=1);

namespace Ep\Helper;

use DateTime;
use DateTimeZone;

final class Date
{
    /**
     * 获得标准格式化时间
     * 
     * @param  int|null $timestamp 时间戳，不传默认为当前时间
     * 
     * @return string
     */
    public static function fromUnix(int $timestamp = null): string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * 获得 GMT 格式时间
     * 
     * @param  int|null $timestamp 时间戳，不传默认为当前时间
     * 
     * @return string
     */
    public static function toGMT(int $timestamp = null): string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        return DateTime::createFromFormat('U', (string) $timestamp)
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('D, d M Y H:i:s') . ' GMT';
    }
}
