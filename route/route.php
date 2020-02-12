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

// post get 请求 提供params

// 无需访问token
Route::group('api/:version/', function(){
     
    // 查看变量 none
    Route::post('user/checkParams','api/:version.User/checkParams');

    // 发送验证码 phone
    Route::post('user/sendcode','api/:version.User/sendCode');
    // 手机登录 phone code
    Route::post('user/phonelogin','api/:version.User/phoneLogin');
    // 账号密码登录 username password
    Route::post('user/login','api/:version.User/login');
    // 第三方登录 见文档
    Route::post('user/otherlogin','api/:version.User/otherLogin');

    // 获取文章分类 （首页） none
    Route::get('postclass', 'api/:version.PostClass/index');
    // 获取话题分类 none
    Route::get('topicclass','api/v1.TopicClass/index');
    // 获取热门话题 none
    Route::get('hottopic','api/v1.Topic/index');
    // 获取指定话题分类下的话题列表 id：话题id， page:页数
    Route::get('topicclass/:id/topic/:page', 'api/v1.TopicClass/topic');
     // 获取文章详情
    Route::get('post/:id', 'api/v1.Post/index');
    // 获取指定话题下的文章列表
    Route::get('topic/:id/post/:page', 'api/v1.Topic/post');
    // 获取指定文章分类下的文章
    Route::get('postclass/:id/post/:page', 'api/v1.PostClass/post');
    // 获取指定用户下的文章
    Route::get('user/:id/post/:page', 'api/v1.User/post');


    // 搜索话题
    Route::post('search/topic', 'api/v1.Search/topic');
    // 搜索文章
    Route::post('search/post', 'api/v1.Search/post');
    // 搜索用户
    Route::post('search/user', 'api/v1.Search/user');

    
    // 广告列表
    Route::get('adsense/:type', 'api/v1.Adsense/index');
});

// 通过headers提供token

// 需要验证token 先通过中间件，不合法不会执行逻辑
Route::group('api/:version/',function(){
    // 退出登录 只需验证token
    Route::post('user/logout','api/:version.User/logout');

})->middleware(['ApiUserAuth']);


// 用户操作（绑定手机）
Route::group('api/:v1/',function(){
    // 上传多图
    Route::post('image/uploadmore','api/:v1.Image/uploadMore');
    // 发布文章
    Route::post('post/create','api/v1.Post/create');
    // 获取指定用户下的所有文章（含隐私）
    Route::get('user/post/:page', 'api/v1.User/Allpost');
})->middleware(['ApiUserAuth','ApiUserBindPhone','ApiUserStatus']);