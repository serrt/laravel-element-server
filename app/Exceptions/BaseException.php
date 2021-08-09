<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class BaseException extends Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function render($request)
    {
        return $request->ajax() ? response()->json([
            'message' => $this->message,
            'code' => $this->code,
        ]) : response();
    }
}
