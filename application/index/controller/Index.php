<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use app\lib\exception\BaseException;

class Index extends Controller
{
   
    public function index()
    {
        // 全局异常抛出示例 throw后实例抛出给ExceptionHandler进行异常处理
        throw new BaseException([ 'msg'=>'load failed', 'errCode'=>999]);
        
        return "111";
    }

    
}
