<?php
namespace Deployer;

require 'recipe/laravel.php';

// 设置代码仓库
set('repository', 'git@github.com:serrt/laravel-element-server.git');

// 复制 vendor 目录
add('copy_dirs', ['vendor']);
before('deploy:vendors', 'deploy:copy_dirs');

localhost()
    ->stage('deepin')
    ->user('panliang')
    ->set('deploy_path', '/home/panliang/www/laravel-deployer');

// 指定运行参数
host('production')
    // ssh 服务器地址
    ->hostname('199.95.25.01')
    // ssh 登录用户
    ->user('root')
    // ssh 登录服务器需要的公钥;如果没有生成, 用密码则需要手动收入
    ->identityFile('~/.ssh/id_rsa')
    // 执行命令的用户, 与服务器上的 nginx 配置的用户保持一致
    ->set('http_user', 'www')
    // 指定代码仓库的分支
    ->set('branch', 'master')
    // 指定服务器上的项目目录
    ->set('deploy_path', '/home/panliang/www/laravel-deployer');

// 默认的没有 route:cache
after('artisan:config:cache', 'artisan:route:cache');

after('deploy:failed', 'deploy:unlock');
before('deploy:symlink', 'artisan:migrate');
