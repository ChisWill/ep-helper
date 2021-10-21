<?php

declare(strict_types=1);

namespace Ep\Tests\Unit;

use Ep\Helper\Curl;
use PHPUnit\Framework\TestCase;

class TestCurl extends TestCase
{
    /**
     * @dataProvider singleGetProvider
     */
    public function testSingleGet($url, $options, $expect)
    {
        $result = Curl::get($url, $options);
        $this->checkResult($result, $expect);
    }

    /**
     * @dataProvider singlePostProvider
     */
    public function testSinglePost($url, $data, $options, $expect)
    {
        $result = Curl::post($url, $data, $options);
        $this->checkResult($result, $expect);
    }

    /**
     * @dataProvider multiGetProvider
     */
    public function testMultiGet($urls, $options, $batch, $expect)
    {
        $results = Curl::getMulti($urls, $options, $batch);
        foreach ($results as $k => $result) {
            $this->checkResult($result, $expect[$k]);
        }
    }

    /**
     * @dataProvider multiPostProvider
     */
    public function testMultiPost($urls, $data, $options, $batch, $expect)
    {
        $result = Curl::postMulti($urls, $data, $options, $batch);
        foreach ($result as $k => $result) {
            $this->checkResult($result, $expect[$k]);
        }
    }

    public function singleGetProvider(): array
    {
        $baseUrl = 'http://ep.cc/demo/request';
        return [
            [
                'url' => $baseUrl . '',
                'options' => [],
                'expect' => [
                    'get' => [],
                    'raw' => ''
                ]
            ], [
                'url' => $baseUrl . '?name=peter',
                'options' => [],
                'expect' => [
                    'get' => ['name' => 'peter'],
                    'raw' => ''
                ]
            ], [
                'url' => $baseUrl . '?name=peter2',
                'options' => [
                    'body' => 'age=123'
                ],
                'expect' => [
                    'raw' => 'age=123',
                    'get' => ['name' => 'peter2']
                ]
            ], [
                'url' => $baseUrl . '?name=peter3',
                'options' => [
                    'body' => 'age=123',
                    'header' => [
                        'Auth' => 'abc'
                    ]
                ],
                'expect' => [
                    'raw' => 'age=123',
                    'get' => ['name' => 'peter3'],
                    'header' => [
                        'Auth' => [
                            'abc'
                        ]
                    ]
                ]
            ],
        ];
    }

    public function singlePostProvider(): array
    {
        $baseUrl = 'http://ep.cc/demo/request';
        return [
            [
                'url' => $baseUrl . '',
                'data' => [
                    'title' => 'good'
                ],
                'options' => [
                    'header' => [
                        'user-auth' => 'abc123'
                    ]
                ],
                'expect' => [
                    'post' => ['title' => 'good'],
                    'get' => [],
                    'header' => [
                        'User-Auth' => [
                            'abc123'
                        ]
                    ]
                ]
            ], [
                'url' => $baseUrl . '?name=mary',
                'data' => [
                    'title' => 'good'
                ],
                'options' => [
                    'header' => [
                        'user-auth' => 'zxc321'
                    ],
                    'type' => Curl::TYPE_FORM_URLENCODED
                ],
                'expect' => [
                    'post' => ['title' => 'good'],
                    'get' => ['name' => 'mary'],
                    'header' => [
                        'Content-Type' => [
                            'application/x-www-form-urlencoded; charset=UTF-8'
                        ],
                        'User-Auth' => [
                            'zxc321'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function multiGetProvider(): array
    {
        $baseUrl = 'http://ep.cc/demo/request';
        $singleProvider = $this->singleGetProvider();
        $count = count($singleProvider) - 1;
        return [
            [
                'urls' => $baseUrl,
                'options' => [],
                'batch' => 1,
                'expect' => [
                    [
                        'get' => []
                    ]
                ]
            ],
            [
                'urls' => $baseUrl . '?name=peter',
                'options' => [
                    'body' => 'age=1'
                ],
                'batch' => 2,
                'expect' => [
                    [
                        'raw' => 'age=1',
                        'get' => ['name' => 'peter']
                    ],
                    [
                        'raw' => 'age=1',
                        'get' => ['name' => 'peter']
                    ]
                ]
            ],
            [
                'urls' => $baseUrl . '?name=bob',
                'options' => [
                    'body' => 'age=12',
                    'header' => [
                        'user-auth' => 'asd321'
                    ]
                ],
                'batch' => 2,
                'expect' => [
                    [
                        'raw' => 'age=12',
                        'get' => ['name' => 'bob'],
                        'header' => [
                            'User-Auth' => [
                                'asd321'
                            ]
                        ]
                    ],
                    [
                        'raw' => 'age=12',
                        'get' => ['name' => 'bob'],
                        'header' => [
                            'User-Auth' => [
                                'asd321'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'urls' => [$baseUrl, $singleProvider[$n = mt_rand(0, $count)]['url']],
                'options' => [[], $singleProvider[$n]['options']],
                'batch' => 1,
                'expect' => [
                    [
                        'get' => []
                    ],
                    $singleProvider[$n]['expect']
                ]
            ],
            [
                'urls' => [$singleProvider[$m = mt_rand(0, $count)]['url'], $singleProvider[$n = mt_rand(0, $count)]['url']],
                'options' => [$singleProvider[$m]['options'], $singleProvider[$n]['options']],
                'batch' => 5,
                'expect' => [
                    $singleProvider[$m]['expect'],
                    $singleProvider[$n]['expect']
                ]
            ],
            [
                'urls' => [$baseUrl . '?name=sai', $baseUrl . '?name=sai2', $baseUrl . '?name=sai3'],
                'options' => [
                    'header' => ['user-auth: abc321'],
                    'body' => 'age=3'
                ],
                'batch' => 2,
                'expect' => [
                    [
                        'raw' => 'age=3',
                        'get' => ['name' => 'sai'],
                        'header' => [
                            'User-Auth' => [
                                'abc321'
                            ]
                        ]
                    ],
                    [
                        'raw' => 'age=3',
                        'get' => ['name' => 'sai2'],
                        'header' => [
                            'User-Auth' => [
                                'abc321'
                            ]
                        ]
                    ],
                    [
                        'raw' => 'age=3',
                        'get' => ['name' => 'sai3'],
                        'header' => [
                            'User-Auth' => [
                                'abc321'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function multiPostProvider(): array
    {
        $baseUrl = 'http://ep.cc/demo/request';
        $singleProvider = $this->singlePostProvider();
        $count = count($singleProvider) - 1;
        return [
            [
                'urls' => $baseUrl . '?a=1',
                'data' => ['desc' => 'hello'],
                'options' => [],
                'batch' => 1,
                'expect' => [
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ]
                    ]
                ]
            ], [
                'urls' => $baseUrl . '?a=1',
                'data' => ['desc' => 'hello'],
                'options' => [],
                'batch' => 2,
                'expect' => [
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ]
                    ],
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ]
                    ]
                ]
            ],
            [
                'urls' => $baseUrl . '?a=1',
                'data' => [http_build_query(['desc' => 'hello']), ['title' => 'wolrd'], ['good' => 'name']],
                'options' => [
                    [
                        'header' => [
                            'ba-xf: 123'
                        ]
                    ], [
                        'type' => Curl::TYPE_FORM_URLENCODED
                    ], []
                ],
                'batch' => 5,
                'expect' => [
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ],
                        'header' => [
                            'Ba-Xf' => ['123'],
                            'Content-Type' => [
                                'application/x-www-form-urlencoded'
                            ],
                        ]
                    ], [
                        'post' => [
                            'title' => 'wolrd'
                        ],
                        'get' => [
                            'a' => '1'
                        ],
                        'header' => [
                            'Content-Type' => [
                                'application/x-www-form-urlencoded; charset=UTF-8'
                            ],
                        ]
                    ], [
                        'post' => [
                            'good' => 'name'
                        ],
                        'get' => [
                            'a' => '1'
                        ],
                        'header' => []
                    ]
                ]
            ], [
                'urls' => [$baseUrl . '?a=1', $baseUrl . '?a=2'],
                'data' => ['desc' => 'hello'],
                'options' => [
                    'header' => [
                        'user-auth' => 'abc'
                    ]
                ],
                'batch' => 12,
                'expect' => [
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ],
                        'header' => [
                            'User-Auth' => [
                                'abc'
                            ]
                        ]
                    ], [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '2'
                        ],
                        'header' => [
                            'User-Auth' => [
                                'abc'
                            ]
                        ]
                    ]
                ]
            ], [
                'urls' => [$singleProvider[$m = mt_rand(0, $count)]['url'], $singleProvider[$n = mt_rand(0, $count)]['url']],
                'data' => [$singleProvider[$m]['data'], $singleProvider[$n]['data']],
                'options' => [$singleProvider[$m]['options'], $singleProvider[$n]['options']],
                'batch' => 5,
                'expect' => [
                    $singleProvider[$m]['expect'],
                    $singleProvider[$n]['expect']
                ]
            ],
        ];
    }

    private function checkResult($result, $expect)
    {
        $result = json_decode($result, true);
        foreach ($expect as $key => $value) {
            if ($key === 'header') {
                foreach ($value as $k => $v) {
                    $this->assertSame($v, $result[$key][$k]);
                }
            } else {
                $this->assertSame($value, $result[$key]);
            }
        }
    }
}
