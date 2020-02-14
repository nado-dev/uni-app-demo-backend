<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Request;
use app\common\controller\BaseController;
use app\common\validate\UserValidate;
use app\common\model\User as UserModel;

class User extends BaseController
{
    // 查看数据
    public function checkParams(){
        return  var_dump( request()->param());
    }


    // 发送验证码
    public function sendCode(){
        // return  var_dump( request()->param());

        // 验证参数
        (new UserValidate())->goCheck('sendCode');
        // 发送验证码逻辑
        (new UserModel())->sendCode();
        return self::showResCodeWithOutData('发送成功');
    }


    // 手机号码登录
    public function phoneLogin(){
        // return  var_dump( request()->param());

        // 验证登录信息
        (new UserValidate())->goCheck('phonelogin');
        // 手机登录
        $token = (new UserModel())->phoneLogin();
        return self::showResCode('登录成功',['token'=>$token]);
    }


    // 账号密码登录
    public function login(){
        // 验证登录信息
        (new UserValidate())->goCheck('login');
        // 登录
        $token = (new UserModel())->login();
        return self::showResCode('登录成功',['token'=>$token]);
    }


        // 第三方登录
    public function otherLogin(){
        // 验证登录信息
        (new UserValidate())->goCheck('otherlogin');
        $token = (new UserModel())->otherlogin();
        return self::showResCode('登录成功',['token'=>$token]);
    }


        // 退出登录
    public function logout(){
        // 退出登录
        (new UserModel())->logout();
        return self::showResCodeWithOutData('退出成功');
    }
    

    // 获取用户发布文章列表
    public function post(){
        (new UserValidate())->goCheck('post'); 
        $list = (new UserModel())->getPostList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
    

        // 获取用户发布文章列表
    public function Allpost(){
        (new UserValidate())->goCheck('allpost'); 
        $list = (new UserModel())->getAllPostList();
        return self::showResCode('获取成功',['list'=>$list]);
    }


    // 绑定手机
    public function bindphone(){
        (new UserValidate())->goCheck('bindphone');
        // 绑定
        (new UserModel())->bindphone();
        return self::showResCodeWithOutData('绑定成功');
    }

        // 绑定邮箱
    public function bindemail(){
        (new UserValidate())->goCheck('bindemail');
        // 绑定
        (new UserModel())->bindemail();
        return self::showResCodeWithOutData('绑定成功');
    }


        // 绑定第三方
    public function bindother(){
        (new UserValidate())->goCheck('bindother');
        (new UserModel())->bindother();
        return self::showResCodeWithOutData('绑定成功');
    }

        // 修改头像
    public function editUserpic(){
        (new UserValidate())->goCheck('edituserpic');      
        (new UserModel())->editUserpic();
        return self::showResCodeWithOutData('修改头像成功');
    }

        // 修改资料
    public function editinfo(){
        (new UserValidate())->goCheck('edituserinfo');
        (new UserModel())->editUserinfo();
        return self::showResCodeWithOutData('修改成功');
    }

        // 修改密码
    public function rePassword(){
        (new UserValidate())->goCheck('repassword'); 
        (new UserModel())->repassword();
        return self::showResCodeWithOutData('修改密码成功');
    }

    // 关注
    public function follow(){
        return 1;
        (new UserValidate())->goCheck('follow'); 
        (new UserModel())->ToFollow();
        return self::showResCodeWithOutData('关注成功');
    }


    // 取消关注
    public function unfollow(){
        (new UserValidate())->goCheck('unfollow'); 
        (new UserModel())->ToUnFollow();
        return self::showResCodeWithOutData('取消关注成功');
    }


    // 互关列表
    public function friends(){
        (new UserValidate())->goCheck('getfriends'); 
        $list = (new UserModel())->getFriendsList();
        return self::showResCode('获取成功',['list'=>$list]);
    }


    // 粉丝列表
    public function fens(){
        (new UserValidate())->goCheck('getfens'); 
        $list = (new UserModel())->getFensList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

        // 关注列表
    public function follows(){
        (new UserValidate())->goCheck('getfollows'); 
        $list = (new UserModel())->getFollowsList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
}
