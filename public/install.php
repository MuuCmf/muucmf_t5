<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ MuuCmf 安装入口文件 ]
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 判断是否安装MuuCmf
if (is_file(APP_PATH . './install.lock'))
{
    header("location:./index.php");
    exit;
}
// 安装数据目录
define('INSTALL_PATH', APP_PATH . 'install/data/');

define('INSTALL_APP_PATH', __DIR__ .'/../');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/base.php';

// 绑定到admin模块
\think\Route::bind('install');

// 执行应用
\think\App::run()->send();
