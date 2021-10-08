<?php

declare(strict_types=1);

namespace Ep\Helper;

use SimpleXMLElement;

class Arr
{
    /**
     * 从数组中获取一组键的值
     * 
     * @param  array $array   待操作数组
     * @param  array $keys    一组键
     * @param  array $default 默认值
     * 
     * @return array
     */
    public static function getValues(array $array, array $keys, array $default = []): array
    {
        return array_intersect_key($array, array_flip($keys)) ?: $default;
    }

    /**
     * 判断数组是否以数字为键
     * ps. 包括空数组
     *
     * @param  array $array       待检查数组
     * @param  bool  $consecutive 检查键是否从0开始
     * 
     * @return bool
     */
    public static function isIndexed(array $array, bool $consecutive = false): bool
    {
        if ($array === []) {
            return true;
        }

        if ($consecutive) {
            return array_keys($array) === range(0, count($array) - 1);
        } else {
            foreach ($array as $key => $value) {
                if (!is_int($key)) {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * 删除数组一项元素并返回，如果不存在则返回给定的默认值
     *
     * @param  array  $array   待操作数组
     * @param  string $key     键名
     * @param  mixed  $default 默认值
     * 
     * @return mixed
     */
    public static function remove(array &$array, string $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            unset($array[$key]);
            return $value;
        }

        return $default;
    }

    /**
     * 构建一个键值对数组
     *
     * Usage:
     *
     * ```php
     * $array = [
     *     ['id' => '1', 'name' => 'a', 'class' => 'x'],
     *     ['id' => '2', 'name' => 'b', 'class' => 'x'],
     *     ['id' => '3', 'name' => 'c', 'class' => 'y'],
     * ];
     *
     * $result = Arr::map($array, 'id', 'name');
     * // the result is:
     * // [
     * //     '1' => 'a',
     * //     '2' => 'b',
     * //     '3' => 'c',
     * // ]
     *
     * $result = Arr::map($array, 'id', 'name', 'class');
     * // the result is:
     * // [
     * //     'x' => [
     * //         '1' => 'a',
     * //         '2' => 'b',
     * //     ],
     * //     'y' => [
     * //         '3' => 'c',
     * //     ],
     * // ]
     * ```
     *
     * @param  array           $array 待操作数组
     * @param  string|int      $from  作为键的字段
     * @param  string|int      $to    作为值的字段
     * @param  string|int|null $group 分组字段
     * 
     * @return array
     */
    public static function map(array $array, $from, $to, $group = null): array
    {
        if ($group === null) {
            return array_column($array, $to, $from);
        }

        $result = [];
        foreach ($array as $item) {
            $groupKey = $item[$group] ?? '';
            $key = $item[$from] ?? '';
            $result[$groupKey][$key] = $item[$to] ?? null;
        }

        return $result;
    }

    /**
     * 合并多个数组，相同键的标量将覆盖，相同键的数组将合并
     * 
     * @param  array $args 要合并的数组
     * 
     * @return array
     */
    public static function merge(array ...$args): array
    {
        $result = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_int($k)) {
                    if (isset($result[$k])) {
                        $result[] = $v;
                    } else {
                        $result[$k] = $v;
                    }
                } elseif (is_array($v) && isset($result[$k]) && is_array($result[$k])) {
                    $result[$k] = static::merge($result[$k], $v);
                } else {
                    $result[$k] = $v;
                }
            }
        }

        return $result;
    }

    /**
     * 数组转 XML
     * 
     * @param  array  $array 待转换数组
     * 
     * @return string
     */
    public static function toXml(array $array): string
    {
        $xml = '<xml>';
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml .= sprintf('<%s>%s</%s>', $key, $val, $key);
            } else {
                $xml .= sprintf('<%s><![CDATA[%s]]></%s>', $key, $val, $key);
            }
        }
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * XML 转数组
     * 
     * @param  string $xml 待转换的 XML 字符
     * 
     * @return array
     */
    public static function fromXml(string $xml): array
    {
        $element = @simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOCDATA);
        if ($element === false) {
            return [];
        }
        return self::getElementValue($element);
    }

    private static function getElementValue(SimpleXMLElement $element): array
    {
        $result = [];
        foreach ($element as $key => $value) {
            /** @var SimpleXMLElement $value */
            $result[$key] = $value->count() === 0 ? $value->__toString() : self::getElementValue($value);
        }
        return $result;
    }
}
