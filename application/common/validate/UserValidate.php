<?php

namespace app\common\validate;

use think\Validate;

class UserValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        //字符串内不能乱加空格
        'phone'=> 'require|mobile',
        // 验证码
        'code' => 'require|number|length:4|isPerfectCode',// isPerfectCode 验证是否匹配
        'username'=>'require',
        'password'=>'require|alphaDash',
        // 第三方有关规则
        // 厂商
        'provider'=>'require',
        // openid
        'openid'=>'require',
        // 昵称
        'nickName'=>'require',
        // 头像
        'avatarUrl'=>'require',
        // 有效期
        'expires_in'=>'require',
        'id'=>'require|integer|>:0',
        'page'=>'require|integer|>:0',
        'email'=>'require|email',
        // 头像
        'userpic'=>'image',
        // 用户资料
        'name'=>'require|chsDash',
        // sex 0女 1男 2未定义
        'sex'=>'require|in:0,1,2',
        'qg'=>'require|in:0,1,2',
        'job'=>'require|chsAlpha',
        'birthday'=>'require|dateFormat:Y-m-d',
        'path'=>'require|chsDash',
        'oldpassword'=>'require',
        'newpassword'=>'require|alphaDash',
        'renewpassword'=>'require|confirm:newpassword',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'phone.require' => '请填写手机号码',
        'phone.mobile' => '手机号码不合法',
        'username.require' => '请填写用户名/昵称/邮箱',
        'password.require' => '请填写密码',
        'password.alphaDash'=>'密码格式不合法',
    ];
    /**
     * 定义场景信息
     * 格式：'场景名'	=>	['字段名1', '字段名2', ...]
     *
     * @var array
     */
    protected $scene = [
        // 发送验证码
        'sendCode' => ['phone'],
        // 手机号登录
        'phonelogin' => ['phone', 'code'],
        // 账号密码登录
        'login' => ['username', 'password'],
        // 第三方登录
        'otherlogin'=>['provider','openid','nickName','avatarUrl','expires_in'],
        // 获取当前用户的文章列表 无需登录 任意用户 权限为公开
        'post'=>['id','page'],
        // 当前已登录用户下的所有文章 
        'allpost'=>['page'],
        // 绑定手机
        'bindphone'=>['phone'],
        // 绑定邮箱
        'bindemail'=>['email'],
        // 绑定第三方
        'bindother'=>['provider','openid','nickName','avatarUrl'],
        // 修改头像
        'edituserpic'=>['userpic'],
        // 修改用户信息
        'edituserinfo'=>['name','sex','qg','job','birthday','path'],
        // 修改用户密码
        'repassword'=>['oldpassword','newpassword','renewpassword'],
    ];
}
