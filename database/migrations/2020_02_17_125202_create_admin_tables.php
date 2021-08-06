<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAdminTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('username')->unique()->comment('登录名');
            $table->string('password')->comment('密码');
            $table->string('name')->comment('姓名');
            $table->string('avatar')->nullable()->comment('头像');
            $table->unsignedInteger('status')->default(1)->comment('状态(1: 正常, 2: 禁用)');
            $table->timestamps();
        });
        DB::statement('alter table `admin_users` comment "管理员"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_users');
    }
}
