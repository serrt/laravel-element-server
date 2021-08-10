<?php

return [
    'access_key' => env('ALIYUN_ACCESS_KEY'),
    'access_secret' => env('ALIYUM_ACCESS_SECRET'),
    'oss' => [
        'endpoint' => 'oss-cn-chengdu.aliyuncs.com',
        'bucket' => 'panliang-laravel-element',
        'domain' => 'oss.abcdefg.fun',
        'schema' => 'https://',
        // 上传成功后的通知地址
        'callback' => '',
        // 上传根目录 / 结尾!!!
        'dir' => 'admin/'
    ]
];
