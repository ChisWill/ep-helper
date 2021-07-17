<?php

declare(strict_types=1);

namespace Ep\Helper;

class System
{
    /**
     * 获取所在方法的调用方法名
     * 
     * @return string
     */
    public static function getCallerMethod(): string
    {
        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'] ?? '';
    }

    /**
     * 获取所在方法的调用者
     * 
     * @return string
     */
    public static function getCallerName(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2] ?? [];
        $class = $trace['class'] ?? '';
        $method = $trace['function'] ?? '';
        if ($class) {
            return $class . $trace['type'] . $method;
        } else {
            return $method;
        }
    }
}
