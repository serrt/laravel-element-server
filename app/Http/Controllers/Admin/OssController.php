<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Facades\OssClient;

class OssController extends Controller
{
    public function config(Request $request)
    {
        $path = $request->input('path', 'uploads');

        $result = OssClient::frontendSign($path);

        return $this->json($result);
    }
}
