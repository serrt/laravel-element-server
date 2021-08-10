<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:admin']], function () {

    // 文件上传
    Route::post('upload', [WebController::class, 'upload']);

    // 表单 unique 验证
    Route::post('unique', [WebController::class, 'unique']);

    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('profile', [AuthController::class, 'update']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('admin-user', AdminUserController::class);
    Route::apiResource('role', RoleController::class);
    Route::get('permission/tree', [PermissionController::class, 'tree']);
    Route::apiResource('permission', PermissionController::class);

    Route::get('activity-log', [ActivityLogController::class, 'index']);
    Route::post('activity-log/clear', [ActivityLogController::class, 'clear']);


    Route::group(['prefix' => 'media'], function () {
        Route::get('file', [MediaController::class, 'files']);
        Route::post('file', [MediaController::class, 'upload']);
        Route::delete('file', [MediaController::class, 'deleteFiles']);

        Route::post('folder', [MediaController::class, 'addFolder']);
        Route::delete('folder', [MediaController::class, 'deleteFolder']);
    });

    Route::post('oss/config', [OssController::class, 'config']);
});
