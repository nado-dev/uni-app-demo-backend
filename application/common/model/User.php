<?php

namespace app\common\model;

use think\Model;
use app\lib\exception\BaseException;
use think\facade\Cache;
use app\common\controller\AliSMSController;

class User extends Model
{
    // 发送验证码
    public function sendCode(){
    // 获取用户提交手机号码
    $phone = request()->param('phone');
    // 判断是否已经发送过  查找缓存
    if(Cache::get($phone)) 
        throw new BaseException(['code'=>200,'msg'=>'你操作得太快了','errorCode'=>30001]);
    // 生成4位验证码
    $code = random_int(1000,9999);
    // 判断是否开启验证码功能 模拟验证开关
    if(!config('api.alisms.isopen')){
        Cache::set($phone,$code,config('api.alisms.expire'));
        throw new BaseException(['code'=>200,'msg'=>'验证码：'.$code,'errorCode'=>30005]);
    }
    //模拟 第一个参数 key 第二个参数 value 第三个参数 有效时间
    // Cache::set($phone,$code,60);
    // throw new BaseException(['code'=>200,'msg'=>'验证码：'.$code,'errorCode'=>30005]);
    // 发送验证码
    $res = AlismsController::SendSMS($phone,$code);
    //发送成功 写入缓存
    if($res['Code']=='OK') 
        return Cache::set($phone,$code,config('api.alisms.expire'));
    // 无效号码
    if($res['Code']=='isv.MOBILE_NUMBER_ILLEGAL') 
        throw new BaseException(['code'=>200,'msg'=>'无效号码','errorCode'=>30002]);
    // 触发日限制
    if($res['Code']=='isv.DAY_LIMIT_CONTROL') 
        throw new BaseException(['code'=>200,'msg'=>'今日你已经发送超过限制，改日再来','errorCode'=>30003]);
    
    // 发送失败
    throw new BaseException(['code'=>200,'msg'=>'发送失败','errorCode'=>30004]);

    }
}
