<?php

declare(strict_types=1);

namespace Ep\Tests\Support;

use Ep\Helper\Url;

class UrlService
{
    public function addParams()
    {
        $url = 'http://a.b?name=la&age=12';
        $params = [
            'add' => 12,
            'extra' => 'content'
        ];

        return Url::addParams($url, $params);
    }
}
