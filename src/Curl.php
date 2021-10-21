<?php

declare(strict_types=1);

namespace Ep\Helper;

use LogicException;

/**
 * Curl 请求的 options 除了`curl_setopt()`数外，以下为便捷选项:
 * - type: 请求类型，取值: json|xml|html|text|form-urlencoded, 除此之外将使用原值
 * - accept: 返回数据类型，取值: json|xml|html|text, 默认json, 除此之外将使用原值
 * - timeout: 执行最大时间，可输入小数，默认为 10 秒
 * - header: 设置头信息参数
 * - body: 请求体参数
 * ps. 如果请求体为数组，且不设置 type 为 form-urlencoded, 则默认类型为 form-data
 * 
 * Multi Curl 使用时，可设置地址和配置，根据两两组合，使用总结如下:
 * - 相同的地址，相同的配置，使用 batch 参数
 * - 相同/不同的地址，不同/相同的配置，每个参数项嵌套一层数组
 * - 当地址和配置都嵌套数组时，数组元素须数量一致
 * - 当地址和配置其中之一为数组时，表示复用不嵌套数组的那一项的值
 * 
 * @author ChisWill
 */
final class Curl
{
    public const TYPE_JSON = 'json';
    public const TYPE_XML = 'xml';
    public const TYPE_HTML = 'html';
    public const TYPE_TEXT = 'text';
    public const TYPE_FORM_URLENCODED = 'form-urlencoded';

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
     * @param  string|array $body    请求体数据
     * @param  array        $options curl选项
     * 
     * @return string|null
     */
    public static function post(string $url, $body = [], array $options = []): ?string
    {
        return self::create($options)
            ->setUrl($url)
            ->setMethod('POST')
            ->setBody($body)
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
     * @param  array|string $urls    可使用一维数组，表示请求多个地址
     * @param  array        $options 可使用二维数组，表示使用多个选项
     * @param  int          $batch   当设置值大于1时，且以上参数都为单组值时，则以同样配置发起请求
     * 
     * @return array
     */
    public static function getMulti($urls, array $options = [], int $batch = 1): array
    {
        return Curl::createMulti(
            self::createMultiOptions($urls, 'GET', null, $options, $batch)
        )
            ->exec();
    }

    /**
     * 并发 POST
     *
     * @param  array|string $urls    可使用一维数组，表示请求多个地址
     * @param  array        $body    可使用二维数组，表示使用多个请求参数
     * @param  array        $options 可使用二维数组，表示使用多个选项
     * @param  int          $batch   当设置值大于1时，且以上参数都为单组值时，则以同样配置发起请求
     * 
     * @return array
     */
    public static function postMulti($urls, array $body, array $options = [], int $batch = 1): array
    {
        return Curl::createMulti(
            self::createMultiOptions($urls, 'POST', $body, $options, $batch)
        )
            ->exec();
    }

    /**
     * 自定义的 http 请求
     *
     * @param  array  $urls    多个请求地址
     * @param  string $method  请求方法
     * @param  array  $body    为空或者与请求地址数量相同的请求体数据
     * @param  array  $options 为空或与请求地址数量相同的请求选项
     * 
     * @return array
     */
    public static function httpMulti(array $urls, string $method, array $body = [], array $options = []): array
    {
        return Curl::createMulti(
            self::createMultiOptions($urls, $method, $body, $options, 1)
        )
            ->exec();
    }

    private array $headers;

    private function initSingle(array $options = []): void
    {
        $this->ch = curl_init();
        $this->headers = (array) Arr::remove($options, 'header', []);

        $type = Arr::remove($options, 'type');
        $this->setHeader('Accept', $this->getMimeType(Arr::remove($options, 'accept', self::TYPE_JSON)));
        if ($type !== null) {
            $this->setHeader('Content-Type', $this->getContentType($type));
        }
        $this->setBody(Arr::remove($options, 'body'));

        $timeout = Arr::remove($options, 'timeout', 10);
        if ($timeout >= 1) {
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
        } else {
            curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $timeout * 1000);
        }

        $this->initDefaultOptions();

        curl_setopt_array($this->ch, $options);
    }

    private string $url;

    /**
     * 设置请求地址
     * 
     * @param  string $url
     * 
     * @return self
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    private string $method = 'GET';

    /**
     * 设置请求方法
     * 
     * @param  string $method
     * 
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @var array|string|null
     */
    private $body = null;

    /**
     * 设置请求体数据
     * 
     * @param array|string|null $body
     */
    public function setBody($body): self
    {
        $this->body = $body;
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
        $this->headers[strtolower($key)] = $value;
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
                $this->ready();

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

    private function initDefaultOptions(): void
    {
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($this->ch, CURLOPT_ENCODING, '');
    }

    private function ready(): self
    {
        if (is_array($this->body) && Arr::getValue($this->headers, 'content-type') === $this->getContentType(self::TYPE_FORM_URLENCODED)) {
            $this->body = http_build_query($this->body);
        }
        $headers = [];
        foreach ($this->headers as $name => $value) {
            $headers[] = is_string($name) ? sprintf('%s: %s', $name, $value) : $value;
        }

        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        if ($this->body !== null) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->body);
        }

        return $this;
    }

    private function getMimeType(string $type): string
    {
        switch ($type) {
            case self::TYPE_JSON:
                return 'application/json';
            case self::TYPE_XML:
                return 'application/xml';
            case self::TYPE_HTML:
                return 'text/html';
            case self::TYPE_TEXT:
                return 'text/plain';
            case self::TYPE_FORM_URLENCODED:
                return 'application/x-www-form-urlencoded';
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
            $baseOptions = Arr::removeKeys($options, [
                CURLOPT_URL,
                CURLOPT_CUSTOMREQUEST,
                CURLOPT_POSTFIELDS
            ]);
            $handle = Curl::create($options)
                ->setBaseValue($baseOptions)
                ->ready();
            curl_multi_add_handle($this->mch, $handle->ch);
            $this->handles[] = $handle;
        }
    }

    private function setBaseValue(array $baseOptions): self
    {
        $this->setUrl($baseOptions[CURLOPT_URL]);
        $this->setMethod($baseOptions[CURLOPT_CUSTOMREQUEST]);
        if ($baseOptions[CURLOPT_POSTFIELDS] !== null) {
            $this->setBody($baseOptions[CURLOPT_POSTFIELDS]);
        }
        return $this;
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
     * @param array|string|null $body
     */
    private static function createMultiOptions($urls, string $method, $body, array $options, int $batch = 1): array
    {
        $isMultiUrl = is_array($urls);
        $isMultiBody = $body && is_array($body) && Arr::isIndexed($body, true);
        $isMultiOption = $options && Arr::isIndexed($options, true);
        if ($isMultiUrl) {
            $list = &$urls;
        } elseif ($isMultiBody) {
            $list = &$body;
        } elseif ($isMultiOption) {
            $list = &$options;
        }

        $result = [];
        if (isset($list)) {
            foreach ($list as $k => $v) {
                $url = $isMultiUrl ? ($urls[$k] ?? null) : $urls;
                if (!$url) {
                    continue;
                }
                $result[$k] = self::fillBaseOption(
                    $isMultiOption ? ($options[$k] ?? []) : $options,
                    $url,
                    $method,
                    $isMultiBody ? ($body[$k] ?? []) : $body
                );
            }
        } else {
            for ($i = 0; $i < $batch; $i++) {
                $result[$i] = self::fillBaseOption($options, $urls, $method, $body);
            }
        }
        return $result;
    }

    /**
     * @param mixed $body
     */
    private static function fillBaseOption(array $options, string $url, string $method, $body = null): array
    {
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_CUSTOMREQUEST] = $method;
        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }
        return $options;
    }
}
