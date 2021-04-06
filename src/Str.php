<?php

declare(strict_types=1);

namespace Ep\Helper;

class Str
{
    /**
     * 移除非字母数字的字符，并转为大驼峰命名形式
     * 
     * @param  string $input 待转换字符
     * 
     * @return string
     */
    public static function toPascalCase(string $input): string
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^\pL\pN]+/u', ' ', $input)));
    }

    /**
     * 驼峰命名形式转为指定分隔符连接形式，非字母数字的字符都会转为分隔符
     * 
     * @param  string $input     待转换字符
     * @param  string $separator 分隔符
     * 
     * @return string
     */
    public static function camelToId(string $input, string $separator = '_', bool $strict = false): string
    {
        return mb_strtolower(trim(preg_replace('/[^\pL\pN]+/u', $separator, preg_replace($strict ? '/[A-Z]/' : '/(?<![A-Z])[A-Z]/', addslashes($separator) . '\0', $input)), $separator));
    }

    /**
     * 长文本截取后缩略显示
     * 
     * @param  string $text   原文本
     * @param  int    $length 显示长度
     * @param  string $suffix 省略后缀
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
     * @param  int    $length 长度
     * @param  array  $types  类型
     * @param  string $custom 自定义字符
     * 
     * @return string
     */
    public static function random(int $length = 16, array $types = ['a', 'A', 'd'], string $custom = ''): string
    {
        $charList = [
            'a' => 'abcdefghijklmnopqrstuvwxyz',
            'A' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'd' => '0123456789',
        ];
        $chars = implode('', Arr::getValues($charList, $types)) . $custom;
        $len = strlen($chars);
        if ($len < $length) {
            $chars = str_repeat($chars, intval(ceil($length / $len)));
        }
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * 根据参数及秘钥生成签名
     * 
     * @param  array  参数
     * @param  string 秘钥
     * 
     * @return string
     */
    public static function getSign(array $params, string $secret, string $algo = 'sha256'): string
    {
        ksort($params);
        $arr = [];
        foreach ($params as $key => $value) {
            $arr[] = $key . '=' . $value;
        }
        return hash_hmac($algo, implode('&', $arr), $secret);
    }
}
