<?php

declare(strict_types=1);

namespace Ep\Helper;

use Closure;
use LogicException;

/**
 * [options]:
 * -type: 请求类型，取值：json|xml|html|text, 除此之外将使用原值
 * -accept: 返回数据类型，取值：json|xml|html|text, 默认json, 除此之外将使用原值
 * -timeout: 执行最大时间，可输入小数，默认为 10 秒
 * -header: 设置头信息参数
 * 
 * 其他 curl 参数，通过 $options 直接按键值对的方式传入
 * 
 * @author ChisWill
 */
final class Curl
{
    /**
     * @var resource
     */
    private $ch;
    /**
     * @var resource
     */
    private $mch;

    private bool $multiple = false;

    private function __construct()
    {
    }

    /**
     * 创建一个 Curl 实例
     * 
     * @param array $options curl选项
     * 
     * @return self
     */
    public static function create(array $options = []): self
    {
        $curl = new Curl();
        $curl->initSingle($options);
        return $curl;
    }

    /**
     * GET 请求
     * 
     * @param  string       $url     请求地址
     * @param  array        $options curl选项
     * 
     * @return string|null
     */
    public static function get(string $url, array $options = []): ?string
    {
        return self::create($options)
            ->setUrl($url)
            ->setMethod('GET')
            ->exec();
    }

    /**
     * POST 请求
     * 
     * @param  string       $url     请求地址
     * @param  string|array $data    请求体数据
     * @param  array        $options curl选项
     * 
     * @return string|null
     */
    public static function post(string $url, $data = [], array $options = []): ?string
    {
        return self::create($options)
            ->setUrl($url)
            ->setMethod('POST')
            ->setData($data)
            ->exec();
    }

    private static function createMulti(array $multiOptions = []): self
    {
        $curl = new Curl();
        $curl->initMultiple($multiOptions);
        $curl->multiple = true;
        return $curl;
    }

    /**
     * 并发 GET
     *
     * @param  array|string $urls     一维数组时，表示请求多个地址
     * @param  array|string $data     二维数组时，表示使用多个请求体数据
     * @param  array        $options  二维数组时，表示使用多个选项
     * @param  int          $batch    当设置值大于1时，且以上参数都为单值时，则以同样配置发起请求
     * 
     * @return array
     */
    public static function getMulti($urls, $data = '', array $options = [], int $batch = 1): array
    {
        $params = self::normalizeMultiple($urls, $data, $options, $batch, static function (array $option, string $url, $data): array {
            $option[CURLOPT_URL] = $url;
            $option[CURLOPT_CUSTOMREQUEST] = 'GET';
            $option[CURLOPT_POSTFIELDS] = $data;
            return $option;
        });

        return Curl::createMulti($params)->exec();
    }

    /**
     * 并发 POST
     *
     * @param  array|string $urls     一维数组时，表示请求多个地址
     * @param  array        $data     二维数组时，表示使用多个请求参数
     * @param  array        $options  二维数组时，表示使用多个选项
     * @param  int          $batch    当设置值大于1时，且以上参数都为单值时，则以同样配置发起请求
     * 
     * @return array
     */
    public static function postMulti($urls, array $data, array $options = [], int $batch = 1): array
    {
        $params = self::normalizeMultiple($urls, $data, $options, $batch, static function (array $option, string $url, $data): array {
            $option[CURLOPT_URL] = $url;
            $option[CURLOPT_CUSTOMREQUEST] = 'POST';
            $option[CURLOPT_POSTFIELDS] = $data;
            return $option;
        });

        return Curl::createMulti($params)->exec();
    }

    private array $headers;

    private function initSingle(array $options = []): void
    {
        $this->ch = curl_init();
        $this->headers = (array) Arr::remove($options, 'header', []);

        $accept = Arr::remove($options, 'accept', 'json');
        $type = Arr::remove($options, 'type');
        $this->setHeader('Accept', $this->getMimeType($accept));
        if ($type !== null) {
            $this->setHeader('Content-Type', $this->getContentType($type));
        }

        $timeout = Arr::remove($options, 'timeout', 10);
        if ($timeout >= 1) {
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
        } else {
            curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $timeout * 1000);
        }

        $this->curlSetDefaultOptions();

        curl_setopt_array($this->ch, $options);
    }

    /**
     * 设置请求地址
     * 
     * @param  string $url
     * 
     * @return self
     */
    public function setUrl(string $url): self
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return $this;
    }

    /**
     * 设置请求方法
     * 
     * @param  string $method
     * 
     * @return self
     */
    public function setMethod(string $method): self
    {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        return $this;
    }

    /**
     * 设置请求体数据
     * 
     * @param array|string $data
     */
    public function setData($data): self
    {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        return $this;
    }

    /**
     * 设置请求头信息
     * 
     * @param  string $key
     * @param  string $value 
     * 
     * @return self
     */
    public function setHeader(string $key, string $value): self
    {
        $this->headers[] = sprintf('%s: %s', $key, $value);
        return $this;
    }

    private array $info;

    /**
     * 发起请求
     *
     * @return string|array|null
     */
    public function exec()
    {
        if ($this->multiple) {
            try {
                $this->execMulti();

                return $this->getResults();
            } finally {
                curl_multi_close($this->mch);
            }
        } else {
            try {
                $this->curlSetHttpHeader();

                return curl_exec($this->ch) ?: null;
            } finally {
                $this->info = curl_getinfo($this->ch);

                curl_close($this->ch);
            }
        }
    }

    /**
     * 获得请求信息
     * 
     * @return array
     * @throws LogicException
     */
    public function getInfo(): array
    {
        if (!isset($this->info)) {
            throw new LogicException('Must be called after Curl::exec().');
        }

        return $this->info;
    }

    /**
     * 获得请求后状态码
     * 
     * @return int
     * @throws LogicException
     */
    public function getHttpCode(): int
    {
        if (!isset($this->info)) {
            throw new LogicException('Must be called after Curl::exec().');
        }

        return $this->info['http_code'];
    }

    private function curlSetDefaultOptions(): void
    {
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($this->ch, CURLOPT_ENCODING, '');
    }

    private function curlSetHttpHeader(): void
    {
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
    }

    private function getMimeType(string $type): string
    {
        switch ($type) {
            case 'json':
                return 'application/json';
            case 'xml':
                return 'application/xml';
            case 'html':
                return 'text/html';
            case 'text':
                return 'text/plain';
            default:
                return $type;
        }
    }

    private function getContentType(string $type): string
    {
        $mimeType = $this->getMimeType($type);
        if ($mimeType === $type) {
            return $mimeType;
        } else {
            return $mimeType . '; charset=UTF-8';
        }
    }

    /**
     * @var array<int,Curl>
     */
    private array $handles = [];

    private function initMultiple(array $multiOptions = []): void
    {
        $this->mch = curl_multi_init();

        foreach ($multiOptions as $options) {
            $handle = Curl::create($options);
            $handle->curlSetHttpHeader();
            curl_multi_add_handle($this->mch, $handle->ch);
            $this->handles[] = $handle;
        }
    }

    private function execMulti(): void
    {
        $active = false;
        do {
            $mrc = curl_multi_exec($this->mch, $active);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($this->mch) !== -1) {
                usleep(50);
            }
            do {
                $mrc = curl_multi_exec($this->mch, $active);
            } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        }
    }

    private function getResults(): array
    {
        $results = [];
        foreach ($this->handles as $key => $handle) {
            if (curl_error($handle->ch) === '') {
                $results[$key] = curl_multi_getcontent($handle->ch);
            }
            curl_multi_remove_handle($this->mch, $handle->ch);
            curl_close($handle->ch);
        }
        return $results;
    }

    /**
     * @param array|string $urls
     * @param array|string $data
     */
    private static function normalizeMultiple($urls, $data, array $options, int $batch, Closure $callback): array
    {
        $result = [];
        $multiUrl = is_array($urls);
        $multiData = $data && is_array($data) && Arr::isIndexed($data);
        $multiOptions = $options && Arr::isIndexed($options);
        if ($multiUrl) {
            $list = &$urls;
        } elseif ($multiData) {
            $list = &$data;
        } elseif ($multiOptions) {
            $list = &$options;
        }
        if (isset($list)) {
            foreach ($list as $k => $v) {
                $url = $multiUrl ? ($urls[$k] ?? '') : $urls;
                if (!$url) {
                    break;
                }
                $row = $multiData ? ($data[$k] ?? []) : $data;
                $opt = $multiOptions ? ($options[$k] ?? []) : $options;
                $result[$k] = call_user_func($callback, $opt, $url, $row);
            }
        } else {
            for ($i = 0; $i < $batch; $i++) {
                $result[$i] = call_user_func($callback, $options, $urls, $data);
            }
        }
        return $result;
    }
}
