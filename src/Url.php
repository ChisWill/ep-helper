<?php

declare(strict_types=1);

namespace Ep\Helper;

class Url
{
    /**
     * 根据已有地址，增加额外参数，如果已存在则覆盖
     *
     * @param  string $url
     * @param  array  $params
     * 
     * @return string
     */
    public static function addParams(string $url, array $params = []): string
    {
        $urlInfo = parse_url($url);
        $queryParams = [];
        if (isset($urlInfo['query'])) {
            parse_str($urlInfo['query'], $queryParams);
        }
        $params += $queryParams;
        return ($queryParams ? substr($url, 0, strpos($url, '?')) : $url) . ($params ? '?' : '') . http_build_query($params);
    }

    /**
     * URL安全的 base64 编码
     * 
     * @param  string $string
     * 
     * @return string
     */
    public static function base64encode(string $input): string
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    /**
     * URL安全的 base64 解码
     * 
     * @param  string $input
     * 
     * @return string
     */
    public static function base64decode(string $input): string
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
