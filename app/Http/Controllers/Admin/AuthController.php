<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\PermissionResource;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Auth};

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'captcha_key' => 'required',
            'captcha_code' => ['required', 'captcha_api:' . $request->input('captcha_key') . ',' . $request->input('captcha_config', 'default')]
        ], [
            'password.required' => '密码必填',
            'username.required' => '用户名必填'
        ]);

        $user = AdminUser::query()->where($request->only(['username']))->first();
        if (!$user) {
            return $this->error('用户名或者密码错误');
        }

        if (Hash::check($request->input('password'), $user->password)) {
            if (!$user->isNormal()) {
                throw ValidationException::withMessages([
                    'username' => '该用户已被禁用',
                ]);
            }
            return $this->attemptUser($user);
        }
        return $this->error('用户名或者密码错误');
    }

    public function profile()
    {
        $user = Auth::guard('admin')->user();
        $permissions = $user->getAllPermissions();

        $data = AdminUserResource::make($user);
        $data->offsetSet('permissions', PermissionResource::collection($permissions));
        return $data;
    }

    public function update(Request $request)
    {
        $user = $this->guard()->user();
        if ($request->filled('avatar')) {
            $user->avatar = $request->input('avatar');
        }
        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar');
        }
        $user->save();

        return AdminUserResource::make($user);
    }

    public function logout()
    {
		try {
            $this->guard()->logout();
            return $this->success();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->error('无效的 access_token');
        }
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }

    protected function attemptUser(AdminUser $user)
    {
        $token = $this->guard()->login($user);

        return $this->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expire' => $this->guard()->factory()->getTTL() * 60,
        ]);
    }
}
