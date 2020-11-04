<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminUser::with($request->input('include', []));

        if ($request->filled('order')) {
            $query->orderBy($request->input('order'), $request->input('_order'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('username', 'like', "%$search%");
        }

        $list = $query->paginate($request->input('per_page'));

        return AdminUserResource::collection($list)->additional(['code' => Response::HTTP_OK, 'message' => '']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:admin_users',
        ]);

        $info = AdminUser::create($request->all());

        if ($request->filled('roles')) {
            $info->roles()->attach($request->input('roles'));
        }

        return AdminUserResource::make($info);
    }

    public function show($id, Request $request)
    {
        $info = AdminUser::with($request->input('include', []))->findOrFail($id);

        return AdminUserResource::make($info);
    }

    public function update(Request $request, $id)
    {
        $info = AdminUser::findOrFail($id);

        $request->validate([
            'username' => [Rule::unique('admin_users', 'username')->ignore($info->id)],
        ]);

        $data = $request->all();
        if ($request->has('status')) {
            $data['status'] = $request->input('status') ? AdminUser::STATUS_NORMAL : AdminUser::STATUS_DISABLE;
        }

        $info->update($data);

        if ($request->filled('roles')) {
            $info->roles()->sync($request->input('roles'));
        }

        if ($request->filled('permissions')) {
            $info->permissions()->sync($request->input('permissions'));
        }

        return AdminUserResource::make($info);
    }

    public function destroy($id)
    {
        if (auth('admin')->id() == $id) {
            return $this->error('不能删除自己');
        }
        $info = AdminUser::findOrFail($id);

        $info->delete();

        return $this->success();
    }
}
