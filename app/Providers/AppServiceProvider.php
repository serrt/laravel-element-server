<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\{Schema, Validator};
use App\Services\{SmsService, OssClient};
use App\Services\Facades\SmsFacade;

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
        Schema::defaultStringLength(191);

        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^1[\d]{10}$/', $value);
        });
        
        Validator::extend('sms', function ($attribute, $value, $parameters, $validator) {
            $key = data_get($parameters, 0, '');
            $phone_key = data_get($parameters, 1, 'phone');
            $phone = data_get($validator->attributes(), $phone_key);
            if (!$phone) {
                return false;
            }

            return SmsFacade::checkCode($phone, $value, $key) === true;
        });
    }
}
