<?php

namespace App\Providers;

use Illuminate\Support\{ServiceProvider, Str};
use Illuminate\Support\Facades\{Schema, Validator, DB, Log};
use App\Services\{SmsService, OssClient};
use App\Services\Facades\SmsFacade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('sms', function ($app) {
            return new SmsService();
        });

        $this->app->singleton('oss', function ($app) {
            return new OssClient();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 设置默认字符串长度, 兼容低版本 mysql
        Schema::defaultStringLength(191);

        // 开启模型懒加载检测, 防止查询出现 n+1
        Model::preventLazyLoading(true);

        // 表单验证, 手机号
        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^1[\d]{10}$/', $value);
        });
        
        // 表单验证, 短信验证码
        Validator::extend('sms', function ($attribute, $value, $parameters, $validator) {
            $key = data_get($parameters, 0, '');
            $phone_key = data_get($parameters, 1, 'phone');
            $phone = data_get($validator->attributes(), $phone_key);
            if (!$phone) {
                return false;
            }

            return SmsFacade::checkCode($phone, $value, $key) === true;
        });

        // 添加sql日志
        DB::listen(function (QueryExecuted $query) {
            $request = request();
            $uri = $request->path();
            if (Str::contains($uri, ['log-viewer']) || app()->runningInConsole()) {
                return;
            }
            $sqlWithPlaceholders = str_replace(['%', '?'], ['%%', '%s'], $query->sql);
            $bindings = $query->connection->prepareBindings($query->bindings);
            $pdo = $query->connection->getPdo();
            $realSql = vsprintf($sqlWithPlaceholders, array_map([$pdo, 'quote'], $bindings));
            $duration = $query->time / 1000;
            if ($duration < 0.001) {
                $duration = round($duration * 1000000) . 'μs';
            } elseif ($duration < 1) {
                $duration = round($duration * 1000, 2) . 'ms';
            }
            Log::debug(sprintf('[%s] %s', $duration, $realSql), [
                'duration' => $duration,
                'path' => $uri,
                'method' => $request->method(),
                'params' => $request->all()
            ]);
        });
    }
}
