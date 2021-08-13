<?php

return [
    'access_key' => env('ALIYUN_ACCESS_KEY'),
    'access_secret' => env('ALIYUM_ACCESS_SECRET'),
    'oss' => [
        // 节点域名 例如: oss-cn-hangzhou.aliyuncs.com
        'endpoint' => env('ALIYUN_OSS_ENDPOINT', ''),
        // 库名 例如: test
        'bucket' => env('ALIYUN_OSS_BUCKET', ''),
        // 自定义域名(选填), 例如: oss.example.com
        'domain' => env('ALIYUN_OSS_DOMAIN', ''),
        // 访问资源协议, 例如: http://
        'schema' => env('ALIYUN_OSS_SCHEMA', 'https://'),
        // 上传成功后的通知地址(可选), 例如: http://example.com/callback
        'callback' => env('ALIYUN_OSS_CALLBACK', ''),
        // 根目录(可选), 例如: admin
        'dir' => env('ALIYUN_OSS_DIR', ''),
    ]
];
