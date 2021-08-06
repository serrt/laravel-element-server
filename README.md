# Laravel-Api

## 环境要求

- mysql >= 5.7
- php >= 7.3
- php extension: fileinfo
- php function: proc_open, putenv
- composer

## 安装

1. `git clone https://panliang:228618@gitee.com/paddy_technology/admin-api.git`
2. `cd admin-api` && `composer install`
3. 创建配置文件, 复制 `.env.example` 为 `.env`
4. `php artisan key:generate`, 赋予目录 `./storage` 和 `./boostrap/cache` 写入权限(777)

```
# 项目地址
APP_URL=http://example.com

# 数据库配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin-iframe
DB_USERNAME=homestead
DB_PASSWORD=secret
```

5. 数据库迁移: `php artisan migrate --seed`
6. 文件上传: `php artisan storage:link` 或者 `ln -s storage/app/pubc public/storage`
7. 生成JWT secret: `php artisan jwt:secret`

## 扩展包

- [arcanedev/log-viewer](https://github.com/ARCANEDEV/LogViewer), 可视化日志管理
- [spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog), 记录操作日志
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission), RBAC权限
- [tymon/jwt-auth](https://jwt-auth.readthedocs.io), JWT认证

## 自定义命令

- `model:fillable {table}`, 打印出 `table` 中的列名, 方便录入 `Model` 中的 `$fillable` 属性
