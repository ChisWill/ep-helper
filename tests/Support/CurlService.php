<?php

declare(strict_types=1);

namespace Ep\Tests\Support;

use Ep\Helper\Curl;

class CurlService
{
    public function custom()
    {
        $curl = Curl::create(['timeout' => 1])
            ->setUrl('http://ep.cc/demo/request')
            ->setMethod('GET')
            ->setHeader('a', '123');

        $result = $curl->exec();
        $info = $curl->getInfo();
        $code = $curl->getHttpCode();
        return [$result, $info, $code];
    }

    public function get()
    {
        return Curl::get('http://ep.cc/demo/request', [
            CURLOPT_POSTFIELDS => 'a=1',
            'type' => Curl::TYPE_HTML
        ]);
    }

    public function post()
    {
        $r1 = Curl::post('http://ep.cc/demo/request', json_encode([
            'age' => 11,
            'name' => 'Mary'
        ]), [
            'type' => Curl::TYPE_JSON
        ]);

        $r2 = Curl::post('http://ep.cc/demo/request', [
            'age' => 11,
            'name' => 'Mary'
        ]);

        $r3 = Curl::post('http://ep.cc/demo/request', http_build_query([
            'age' => 11,
            'name' => 'Mary'
        ]));
        return [$r1, $r2, $r3];
    }

    public function getMulti()
    {
        return Curl::getMulti('http://ep.cc/demo/request?ab=1&xc=abc', [
            'type' => Curl::TYPE_JSON,
            'body' => 'age=1',
            'header' => [
                'a-t' => 'x'
            ]
        ], 3);
    }

    public function postMulti()
    {
        return Curl::postMulti('http://ep.cc/demo/request', [json_encode([
            'a' => 1
        ])], [
            'type' => Curl::TYPE_JSON
        ], 3);
    }
}
