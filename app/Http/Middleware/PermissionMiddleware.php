<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\{Request};
use App\Exceptions\PermissionException;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission, $guard = 'admin')
    {
        $user = auth($guard)->user();
        if ($user->can($permission, $guard) || $user->hasPermissionTo($permission, $guard)) {
            return $next($request);
        }

        throw PermissionException::forPermission($permission);
    }
}
