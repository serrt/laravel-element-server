<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class OssClient extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'oss';
    }
}

