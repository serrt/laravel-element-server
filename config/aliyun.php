<?php

return [
    'access_key' => env('ALIYUN_ACCESS_KEY'),
    'access_secret' => env('ALIYUM_ACCESS_SECRET'),
    'oss' => [
        // 节点域名 例如: oss-cn-hangzhou.aliyuncs.com
        'endpoint' => env('ALIYUN_OSS_ENDPOINT', 'oss-cn-chengdu.aliyuncs.com'),
        // 库名 例如: test
        'bucket' => env('ALIYUN_OSS_BUCKET', 'panliang-laravel-element'),
        // 自定义域名(选填), 例如: oss.example.com
        'domain' => env('aliyun_oss_domain', 'oss.abcdefg.fun'),
        // 访问资源协议, 例如: http://
        'schema' => 'https://',
        // 上传成功后的通知地址(可选), http://callback.example.com
        'callback' => '',
        // 根目录(可选), 例如: admin
        'dir' => ''
    ]
];
