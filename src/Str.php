<?php

declare(strict_types=1);

namespace Ep\Helper;

final class Str
{
    /**
     * 默认为去除字符串前缀，非严格模式下去除指定字符第一次出现前的所有内容
     * 
     * @param  string $input
     * @param  string $prefix
     * @param  bool   $strict
     * 
     * @return string
     */
    public static function ltrim(string $input, string $prefix, bool $strict = true): string
    {
        $pos = strpos($input, $prefix);
        if ($pos === false) {
            return $input;
        }
        if ($strict === true && $pos !== 0) {
            return $input;
        }
        return substr($input, strlen($prefix) + $pos);
    }

    /**
     * 默认为去除字符串后缀，非严格模式下去除指定字符最后一次出现后的所有内容
     * 
     * @param  string $input
     * @param  string $suffix
     * @param  bool   $strict
     * 
     * @return string
     */
    public static function rtrim(string $input, string $suffix, bool $strict = true): string
    {
        $pos = strrpos($input, $suffix);
        if ($pos === false) {
            return $input;
        }
        if ($strict === true && isset($input[strlen($suffix) + $pos])) {
            return $input;
        }
        return substr($input, 0, $pos);
    }

    /**
     * 移除非字母数字的字符，并转为大驼峰命名形式
     * 
     * @param  string $input
     * 
     * @return string
     */
    public static function toPascalCase(string $input): string
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^\pL\pN]+/u', ' ', $input)));
    }

    /**
     * 将驼峰命名的形式转为指定分隔符的小写字母连接形式
     * - 严格模式下，每个大写字母都会被分割
     * - 非严格模式下，连续的大写字母将作为整体进行分割
     * 
     * @param  string $input
     * @param  string $separator
     * @param  bool   $strict
     * 
     * @return string
     */
    public static function camelToId(string $input, string $separator = '_',  bool $strict = false): string
    {
        return mb_strtolower(trim(
            preg_replace_callback(
                '/[^\pL\pN]' . '\\' . $separator . '/u',
                static fn ($v) => trim($v[0], $separator),
                preg_replace(
                    $strict ? '/[A-Z]/' : '/(?<![A-Z])[A-Z]/',
                    addslashes($separator) . '\0',
                    $input
                )
            ),
            $separator
        ));
    }

    /**
     * 长文本截取后缩略显示
     * 
     * @param  string $text
     * @param  int    $length
     * @param  string $suffix
     * 
     * @return string
     */
    public static function subtext(string $text, int $length, string $suffix = '...'): string
    {
        if (mb_strlen($text, 'UTF-8') > $length) {
            return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
        } else {
            return $text;
        }
    }

    /**
     * 生成指定位数的随机字符串
     * 
     * @param  int    $length
     * @param  array  $types
     * @param  string $custom
     * 
     * @return string
     */
    public static function random(int $length = 16, array $types = ['a', 'A', 'd'], string $custom = ''): string
    {
        $list = [
            'a' => 'abcdefghijklmnopqrstuvwxyz',
            'A' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'd' => '0123456789',
        ];
        $chars = implode('', Arr::getValues($list, $types)) . $custom;
        $len = strlen($chars);
        if ($len < $length) {
            $chars = str_repeat($chars, intval(ceil($length / $len)));
        }
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * 根据参数及秘钥生成签名
     * 
     * @param  array  $params
     * @param  string $secret
     * @param  string $algo
     * 
     * @return string
     */
    public static function getSign(array $params, string $secret, string $algo = 'sha256'): string
    {
        ksort($params);
        $pieces = [];
        foreach ($params as $key => $value) {
            $pieces[] = $key . '=' . $value;
        }
        return hash_hmac($algo, implode('&', $pieces), $secret);
    }
}
