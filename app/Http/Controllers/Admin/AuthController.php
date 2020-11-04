<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\PermissionResource;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'username' => 'required_without:phone'
        ], [
            'password.required' => '密码必填',
            'username.required_without' => '用户名 或者 电话 必填'
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
        $user = Auth::guard('admin')->user();
        if ($request->filled('avatar')) {
            $user->avatar = $request->input('avatar');
        }
        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }
        if ($request->filled('password')) {
            $user->password = Hash::make( $request->input('password'));
        }
        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar');
        }
        $user->save();

        return AdminUserResource::make($user);
    }

    public function logout()
    {
        $user = Auth::guard('admin')->user();
        $user->api_token = null;
        $user->save();
        return $this->success();
    }

    protected function attemptUser(AdminUser $user)
    {
        if (!$user->api_token) {
            $user->api_token = Str::random(32);
            $user->save();
        }

        $user->offsetSet('auth_token', 1);

        return $this->success(['api_token' => $user->api_token]);
    }
}
