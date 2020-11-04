<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")->orWhere('display_name', 'like', "%$search%");
            });
        }

        if ($request->filled('order')) {
            $query->orderBy($request->input('order'), $request->input('_order'));
        }

        $list = $query->paginate($request->input('per_page'));

        return RoleResource::collection($list)->additional(['code' => Response::HTTP_OK, 'message' => '']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'display_name' => 'required'
        ], [
            'name.unique' => '该角色已经存在'
        ]);

        $role = Role::create($request->all());
        $role->permissions()->sync($request->input('permissions'));

        return RoleResource::make($role);
    }

    public function show($id, Request $request)
    {
        $role = Role::with($request->input('include', []))->findOrFail($id);

        return RoleResource::make($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::query()->findOrFail($id);
        $request->validate([
            'name' => Rule::unique('roles', 'name')->ignore($role->id)
        ], [
            'name.unique' => '该角色已经存在'
        ]);

        $role->update($request->all());

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->input('permissions'));
        }

        return RoleResource::make($role);
    }

    public function destroy($id)
    {
        $role = Role::query()->findOrFail($id);

        // 删除关联的 role_permissions
        $role->permissions()->detach();

        $role->delete();

        return $this->success();
    }
}
