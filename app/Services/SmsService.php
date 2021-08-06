<?php

namespace App\Services;

use Illuminate\Support\Facades\{Cache};

class SmsService
{
    // 每个手机号,每一分钟只能发送一条验证码,每条验证码有效时间3分钟,当连续发送超过5条时,需要再等4分钟
    protected $min_code_time = 1;
    protected $expires_time = 3;
    protected $max_code_time = 4;
    protected $max_code_num = 5;

    /**
     * 发送验证码短信,并校验发送次数
     *
     * @param $phone string 手机号
     * @param $keyid string 区分不同的业务
     * @return bool|string
     * @throws \Exception
     */
    public function sendCode($phone, $keyid = '')
    {
        $key = $this->getCacheKey($phone, $keyid);
        /*
         * 缓存每个手机号的短信验证码记录
         * [
         *  ['code'=>验证码, 'expires_time'=>有效时间戳, 'send_time'=>发送时间戳]
         *  ....
         * ]
         */
        $send_log = collect(cache($key));
        $now = now();
        if ($send_log->where('send_time', '>=', $now->copy()->subMinute($this->min_code_time))->count() > 0) {
            return '验证码已经发送,请稍后再试';
        }
        if ($send_log->count() >= $this->max_code_num) {
            return '验证码发送频繁,请稍后再试';
        }

        // 测试环境不发送验证码,默认: 123456
        if ($this->debug()) {
            $code = 123456;
        } else {
            $code = mt_rand(100000, 999999);
        }

        $result = $this->sendSms($phone, ['code' => $code]);
        if ($result !== true) {
            return $result;
        }

        // 缓存验证码3分钟
        $expires_time = $now->copy()->addMinute($this->expires_time);
        $send_log->push(['code'=>$code, 'expires_time'=>$expires_time, 'send_time'=>$now]);
        cache([$key=>$send_log->toArray()], $now->copy()->addMinute($this->max_code_time));
        return true;
    }

    /**
     * 校验验证码是否有效
     * 请在验证成功, 业务处理完成后, 调用清除验证码的方法 !!!
     *
     * @param $phone string 手机号
     * @param $code string 输入的验证码
     * @param $keyid string 发送短信的keyid
     * @return bool|string
     * @throws \Exception
     */
    public function checkCode($phone, $code, $keyid = '')
    {
        $key = $this->getCacheKey($phone, $keyid);
        $cache_log = collect(cache($key));

        $count = $cache_log->where('expires_time', '>=', now())->where('code', $code)->count();

        return $count > 0 ? true : '验证码不正确或已过期';
    }

    /**
     * 清空缓存的验证码
     *
     * @param $phone string 手机号
     * @param $keyid string keyid
     * @return mixed
     */
    public function clearCode($phone, $keyid = '')
    {
        return Cache::forget($this->getCacheKey($phone, $keyid));
    }

    /**
     * 获取缓存的key
     * @param $phone string 手机号
     * @param $key string 关键字(keyid)
     * @return string
     */
    protected function getCacheKey($phone, $key = '')
    {
        return $phone.($key ? '_sms_'.$key : '_sms');
    }

    /**
     * 调试模式
     * true: 验证码固定 123456
     * false: 生成随机验证码
     */
    protected function debug()
    {
        return config('app.debug');
    }

    /**
     * 发送短信验证码
     *
     * @param string $phone 手机号
     * @param array $params 替换内容{code: 验证码}
     * @return mixed true: 发送成功, string: 返回错误信息
     */
    protected function sendSms($phone, $params = [])
    {
        return true;
    }
}
