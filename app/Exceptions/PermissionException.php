<?php

namespace App\Exceptions;

use Illuminate\Http\{Response};

class PermissionException extends BaseException
{
    protected $code = Response::HTTP_FORBIDDEN;

    protected $permission;

    public static function forPermission($permission)
    {
        $e = new self('没有权限, ' . $permission);
        $e->setPermission($permission);
        return $e;
    }

    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->message,
            'code' => $this->code,
            'permission' => $this->permission
        ]);
    }
}
