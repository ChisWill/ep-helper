<?php

declare(strict_types=1);

namespace Ep\Helper;

use SimpleXMLElement;

final class Arr
{
    /**
     * Usage examples,
     * 
     * ```php
     * $user = [
     *     'id' => 1,
     *     'address' => [
     *         'street' => 'home'
     *     ],
     *     '1.0' => [
     *         '2.0' => 'version'
     *     ]
     * ];
     * $street = Arr::getValue($user, 'address.street', 'defaultValue');
     * $version = Arr::getValue($user, ['1.0', '2.0'], 'defaultValue');
     * // $street is 'home', $version is version
     * ```
     *
     * @param  array            $array
     * @param  array|string|int $key
     * @param  mixed            $default
     * 
     * @return mixed
     */
    public static function getValue(array $array, $key, $default = null)
    {
        if (is_scalar($key) && array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (is_int($key)) {
            return $default;
        }

        if (is_string($key)) {
            if (strpos($key, '.') === false) {
                return $default;
            }
            $key = explode('.', $key);
        }

        if (!is_array($key)) {
            return $default;
        }

        foreach ($key as $k) {
            if (array_key_exists($k, $array)) {
                $array = $array[$k];
            } else {
                return $default;
            }
        }
        return $array;
    }

    /**
     * 从数组中获取一组键的值，如果获取不到，则尝试从 $default 中获取，还获取不到则返回`null`
     * 
     * @param  array $array
     * @param  array $keys
     * @param  array $default
     * 
     * @return array
     */
    public static function getValues(array $array, array $keys, array $default = []): array
    {
        $flipKeys = array_flip($keys);
        $values = array_intersect_key($array, $flipKeys);
        foreach (array_diff_key($flipKeys, $values) as $key => $i) {
            $values[$key] = $default[$key] ?? null;
        }
        return $values;
    }

    /**
     * 判断数组是否以数字为键，空数组也视为`true`
     *
     * @param  array $array
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
     * @param  array      $array
     * @param  string|int $key
     * @param  mixed      $default
     * 
     * @return mixed
     */
    public static function remove(array &$array, $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            unset($array[$key]);
            return $value;
        }

        return $default;
    }

    /**
     * 删除数组中指定的键并返回，如果获取不到，则尝试从 $default 中获取，还获取不到则返回`null`
     * 
     * @param  array $array
     * @param  array $keys
     * @param  array $default
     * 
     * @return array
     */
    public static function removeKeys(array &$array, array $keys, array $default = []): array
    {
        try {
            return self::getValues($array, $keys, $default);
        } finally {
            foreach ($keys as $key) {
                unset($array[$key]);
            }
        }
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
     * @param  iterable        $array
     * @param  string|int      $from
     * @param  string|int      $to
     * @param  string|int|null $group
     * 
     * @return array
     */
    public static function map(iterable $array, $from, $to, $group = null): array
    {
        if ($group === null && is_array($array)) {
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
     * @param  array $arrays
     * 
     * @return array
     */
    public static function merge(array ...$arrays): array
    {
        $result = array_shift($arrays);
        while (!empty($arrays)) {
            $next = array_shift($arrays);
            foreach ($next as $k => $v) {
                if (is_int($k)) {
                    if (isset($result[$k])) {
                        $result[] = $v;
                    } else {
                        $result[$k] = $v;
                    }
                } elseif (is_array($v) && isset($result[$k]) && is_array($result[$k])) {
                    $result[$k] = self::merge($result[$k], $v);
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
     * @param  array $array
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
     * @param  string $xml
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
        /** @var iterable<string,SimpleXMLElement> $element */
        foreach ($element as $key => $value) {
            $result[$key] = $value->count() === 0 ? $value->__toString() : self::getElementValue($value);
        }
        return $result;
    }
}
