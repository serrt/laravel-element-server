<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::with($request->input('include', []));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")->orWhere('display_name', 'like', "%$search%");
            });
        }

        if ($request->has('pid')) {
            $query->where('pid', $request->input('pid'));
        }

        if ($request->filled('order')) {
            $query->orderBy($request->input('order'), $request->input('_order'));
        }

        $list = $query->paginate($request->input('per_page'));

        return PermissionResource::collection($list)->additional(['code' => Response::HTTP_OK, 'message' => '']);
    }

    public function tree()
    {
        $list = Permission::query()->where('guard_name', 'admin')->get(['id', 'pid', 'name', 'display_name']);

        $data = $this->getChildren($list, 0);

        return $this->json($data);
    }

    protected function getChildren($list, $pid)
    {
        $data = $list->where('pid', $pid);

        $new_data = [];
        foreach ($data as $item) {
            $item['children'] = $this->getChildren($list, $item->id);
            array_push($new_data, $item);
        }

        return $new_data;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'display_name' => 'required'
        ],[
            'name.required' => '权限 name 必填',
            'name.unique' => '权限 name 已经存在'
        ]);

        $info = Permission::create($request->all());

        return PermissionResource::make($info);
    }

    public function show($id, Request $request)
    {
        $info = Permission::with($request->input('include', []))->findOrFail($id);

        return PermissionResource::make($info);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::query()->findOrFail($id);

        $request->validate([
            'name' => ['required', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'display_name' => 'required'
        ], [
            'name.required' => '权限 name 必填',
            'name.unique' => '权限 name 已经存在'
        ]);

        $permission->update($request->all());

        return PermissionResource::make($permission);
    }

    public function destroy($id)
    {
        $permission = Permission::query()->findOrFail($id);

        // 权限子级
        $permissions = $permission->children->push($permission);

        // 移除用户拥有的该权限
        $permission->users->map(function ($item) use ($permissions) {
            $item->revokePermissionTo($permissions);
        });

        // 移除角色拥有的该权限
        $permission->roles->map(function ($item) use ($permissions) {
            $item->revokePermissionTo($permissions);
        });

        $permission->children()->delete();
        $permission->delete();

        return $this->success();
    }
}
