<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


// 动态改变版本号：/version

// 无需访问token
Route::group('api/:version/', function(){
    // 查看变量
    Route::post('user/checkParams','api/:version.User/checkParams');
    // 发送验证码 
    Route::post('user/sendcode','api/:version.User/sendCode');
    // 手机登录
    Route::post('user/phonelogin','api/:version.User/phoneLogin');
    // 账号密码登录
    Route::post('user/login','api/:version.User/login');
    // 第三方登录
	Route::post('user/otherlogin','api/:version.User/otherLogin');
});


// 需要验证token 先通过中间件，不合法不会执行逻辑
Route::group('api/:version/',function(){
    // 退出登录 只需验证token
    Route::post('user/logout','api/:version.User/logout');
})->middleware(['ApiUserAuth']);