<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OssController extends Controller
{
    public function config(Request $request)
    {
        $path = $request->input('path', 'uploads');

        $config = config('aliyun');

        // 上传地址
        $host = data_get($config, 'oss.schema') . data_get($config, 'oss.bucket'). '.' . data_get($config, 'oss.endpoint');

        // 文件访问域名
        $domain = $host;
        if (data_get($config, 'oss.domain')) {
            $domain = data_get($config, 'oss.schema') . data_get($config, 'oss.domain');
        }
        
        // 允许上传时间(s)
        $expire = 30;
        $end = time() + $expire;
        
        // 上传成功后, 通知地址
        $callback_url = data_get($config, 'oss.callback');
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
        $dir = data_get($config, 'oss.dir') . $path;

        $arr = [
            'expiration' => str_replace('+00:00', '.000Z', gmdate('c', $end)),
            'conditions' => [
                ['content-length-range', 0, $max_size],
                ['starts-with', '$key', $dir]
            ]
        ];

        // 签名
        $sign_str = base64_encode(json_encode($arr));
        $signature = base64_encode(hash_hmac('sha1', $sign_str, data_get($config, 'access_secret'), true));

        $result = [
            'accessid' => data_get($config, 'access_key'),
            'host' => $host,
            'policy' => $sign_str,
            'signature' => $signature,
            'expire' => $end,
            'callback' => $base64_callback_body,
            'dir' => $dir,
            'domain' => $domain,
        ];

        return $this->json($result);
    }
}
