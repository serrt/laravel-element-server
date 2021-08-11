<?php

namespace App\Services;

use Illuminate\Config\Repository;
use Illuminate\Support\Str;

class OssClient
{
    protected Repository $config;

    function __construct()
    {
        $this->config = new Repository(config('aliyun'));
    }

    /**
     * 签名给前端直传
     * 
     * @param string $path 目录
     * @param int $expire 上传时间(默认: 30s)
     * 
     * @return array
     */
    public function frontendSign($path = '', $expire = 30)
    {
        $config = $this->config;

        // 上传接口URL
        $host = $this->getHost();

        // 文件访问域名
        $domain = $this->getDomain();
        
        // 允许上传时间(s)
        $expire = 30;
        $end = time() + $expire;
        
        // 上传成功后, 通知地址
        $callback_url = $config->get('oss.callback');
        $base64_callback_body = '';
        if ($callback_url) {
            $base64_callback_body = base64_encode(json_encode([
                'callbackUrl' => $callback_url,
                'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
                'callbackBodyType' => "application/x-www-form-urlencoded"
            ]));
        }

        // 文件大小(byte) 1000M
        $max_size = 1048576000;

        // 文件上传目录
        $baseDir = $config->get('oss.dir');
        $dir = $baseDir . (Str::endsWith($baseDir, '/' ? '' : '/')) . $path;

        $arr = [
            'expiration' => str_replace('+00:00', '.000Z', gmdate('c', $end)),
            'conditions' => [
                ['content-length-range', 0, $max_size],
                ['starts-with', '$key', $dir]
            ]
        ];

        // 签名
        $sign_str = base64_encode(json_encode($arr));
        $signature = $this->getSignature($sign_str);

        $result = [
            'accessid' => $config->get('access_key'),
            'host' => $host,
            'policy' => $sign_str,
            'signature' => $signature,
            'callback' => $base64_callback_body,
            'expire' => $end,
            'dir' => $dir,
            'domain' => $domain,
        ];

        return $result;
    }

    public function getSignature(string $str)
    {
        return base64_encode(hash_hmac('sha1', $str, $this->config->get('access_secret'), true));
    }

    public function getHost()
    {
        $list = $this->config->getMany(['oss.schema', 'oss.bucket', 'oss.endpoint']);
        return $list['oss.schema'] . $list['oss.bucket'] . '.' . $list['oss.endpoint'];
    }

    public function getDomain()
    {
        $domain = $this->config->get('domain');
        if ($domain) {
            $domain = $this->config->get('oss.schema') . $domain;
        } else {
            $domain = $this->getHost();
        }

        return $domain;
    }
}
