<?php
namespace app\lib\exception;
use Exception;

class BaseException extends Exception
{
    // 默认的异常状态描述
    public $code = 400;
    public $msg = '异常';
    public $errorCode = 999;
    // 如果收到了传来的其他参数，则更新状态描述
    public function __construct($params = []){
        if (!is_array($params)) return;
        if (array_key_exists('code',$params))  $this->code = $params['code'];
        if (array_key_exists('msg',$params))  $this->msg = $params['msg'];
        if (array_key_exists('errorCode',$params))  $this->errorCode = $params['errorCode'];
    }
}
