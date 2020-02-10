<?php

namespace app\common\controller;

use think\Controller;
use think\Request;
// 基类控制器
class BaseController extends Controller
{
    // api 统一返回格式 有data 
   static public function showResCode($msg = 'unknown', $data = [], $code = 200){
       $res = [
            'msg'=> $msg,
            'data'=> $data
       ];
       return json($res, $code);
   }

    // api 统一返回格式 无无数据 只含提示信息
    static public function showResCodeWithoutData($msg = 'unknown', $code = 200){
        return self::showResCode($msg, [], $code);
    }   
}
