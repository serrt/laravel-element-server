<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{AdminUser, Role};
use \Illuminate\Support\Facades\DB;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        AdminUser::query()->truncate();
        Role::query()->truncate();

        $users = [
            ['name' => 'Admin', 'username' => 'admin', 'password' => '$2y$10$ZJWy2T22pzl51ShunrRuTOGNoSpBQ96rHJTb14dY12Za2TQr8vui6']
        ];
        foreach($users as $user) {
            AdminUser::query()->create($user);
        }

        $roles = [
            ['name' => 'admin', 'display_name' => '管理员', 'guard_name' => 'admin']
        ];
        foreach($roles as $role) {
            Role::query()->create($role);
        }
    }
}
