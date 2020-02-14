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
    Route::get('topicclass','api/:version.TopicClass/index');
    // 获取热门话题 none
    Route::get('hottopic','api/:version.Topic/index');
    // 获取指定话题分类下的话题列表 id：话题id， page:页数
    Route::get('topicclass/:id/topic/:page', 'api/:version.TopicClass/topic');
     // 获取文章详情
    Route::get('post/:id', 'api/:version.Post/index');
    // 获取指定话题下的文章列表
    Route::get('topic/:id/post/:page', 'api/:version.Topic/post');
    // 获取指定文章分类下的文章
    Route::get('postclass/:id/post/:page', 'api/:version.PostClass/post')->middleware(['ApiGetUserid']);
    // 获取指定用户下的文章
    Route::get('user/:id/post/:page', 'api/:version.User/post');


    // 搜索话题
    Route::post('search/topic', 'api/:version.Search/topic');
    // 搜索文章
    Route::post('search/post', 'api/:version.Search/post');
    // 搜索用户
    Route::post('search/user', 'api/:version.Search/user');

    
    // 广告列表
    Route::get('adsense/:type', 'api/:version.Adsense/index');

    // 获取当前文章的所有评论
    Route::get('post/:id/comment','api/:version.Post/comment');
    Route::post('update','api/:version.Update/update');
});

// 通过headers提供token

// 需要验证token 先通过中间件，不合法不会执行逻辑
Route::group('api/:version/',function(){
    // 退出登录 只需验证token
    Route::post('user/logout','api/:version.User/logout');

    // 绑定手机
    Route::post('user/bindphone','api/:version.User/bindphone');

})->middleware(['ApiUserAuth']);


// 用户操作（绑定手机）
Route::group('api/:v1/',function(){
    // 上传多图
    Route::post('image/uploadmore','api/:v1.Image/uploadMore');
    // 发布文章
    Route::post('post/create','api/v1.Post/create');
    // 获取指定用户下的所有文章（含隐私）
    Route::get('user/post/:page', 'api/v1.User/Allpost');
    
    // 绑定邮箱
    Route::post('user/bindemail','api/v1.User/bindemail');
    // 绑定第三方
    Route::post('user/bindother','api/v1.User/bindother');

    // 用户顶踩
    Route::post('support', 'api/v1.Support/index');

    // 用户评论
    Route::post('post/comment','api/v1.Comment/comment');

    // 编辑头像
    Route::post('edituserpic','api/v1.User/editUserpic');
    // 编辑资料
    Route::post('edituserinfo','api/v1.User/editinfo');
    // 修改密码
    Route::post('repassword','api/v1.User/rePassword');
    

    // 加入黑名单
    Route::post('addblack','api/:v1.Blacklist/addBlack');
    // 移出黑名单
    Route::post('removeblack','api/:v1.Blacklist/removeBlack');

    // 关注
    Route::post('follow','api/v1.User/follow');
    // 取消关注
    Route::post('unfollow','api/v1.User/unfollow');
    // 互关列表
    Route::get('friends/:page','api/v1.User/friends');
    // 粉丝列表
    Route::get('fens/:page','api/v1.User/fens');
    // 关注列表
    Route::get('follows/:page','api/v1.User/follows');

    // 用户反馈
    Route::post('feedback','api/:v1.Feedback/feedback');

    // 获取用户反馈列表
    Route::get('feedbacklist/:page','api/:v1.Feedback/feedbacklist');
})->middleware(['ApiUserAuth','ApiUserBindPhone','ApiUserStatus']);