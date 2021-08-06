<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Permission, AdminUser, Role};
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        activity()->disableLogging();
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        Permission::query()->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();

        $list = [
            ['name' => 'admin', 'display_name' => '全部权限'],
            ['name' => 'auth', 'display_name' => '权限管理', 'children' => [
                ['name' => 'auth.admin_user', 'display_name' => '账户'],
                ['name' => 'auth.role', 'display_name' => '角色'],
                ['name' => 'auth.permission', 'display_name' => '权限'],
            ]],
            ['name' => 'media', 'display_name' => '媒体管理'],
            ['name' => 'activity-log', 'display_name' => '操作日志'],
        ];

        $this->createByData($list);

        $role_has_permissions = [
            'admin' => ['admin']
        ];
        $model_has_roles = [
            'admin' => ['admin']
        ];

        foreach($model_has_roles as $key => $item) {
            $user = AdminUser::where('username', $key)->first();
            if ($user) {
                $roles = Role::whereIn('name', $item)->get();
                $user->assignRole($roles);
            }
        }

        foreach($role_has_permissions as $key => $item) {
            $role = Role::where('name', $key)->first();
            if ($role) {
                $role->givePermissionTo($item);
            }
        }
    }

    protected function createByRoute()
    {
        // 获取路由上的所有路由
        $route_list = app('router')->getRoutes()->getRoutes();
        foreach ($route_list as $item) {
            $action = $item->action;
            // 路由必须命名
            if (!isset($action['as'])) {
                continue;
            }

            // 取出admin下面的路由
            if (strpos($action['prefix'], 'admin') !== false) {
                Permission::query()->updateOrCreate([
                    'name' => $action['as'],
                ], [
                    'guard_name' => 'admin',
                    'display_name' => __('permission.'.$action['as'])
                ]);
            }
        }
    }

    protected function createByData($data, $guard = 'admin', $pid = 0)
    {
        foreach ($data as $item) {
            $permission = Permission::query()->updateOrCreate([
                'name' => $item['name'],
                'guard_name' => $guard
            ], [
                'display_name' => $item['display_name'],
                'pid' => $pid
            ]);

            if (isset($item['children'])) {
                $this->createByData($item['children'], $guard, $permission->id);
            }
        }
    }
}
