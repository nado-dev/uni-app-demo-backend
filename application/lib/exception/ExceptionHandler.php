<?php
namespace app\lib\exception;
use Exception;
use think\exception\Handle;

/******************************************************** */
/*********           终止程序的方式 抛出异常      ******* */
/******************************************************** */
class ExceptionHandler extends Handle
{
    public $code;
    public $msg;
    public $errorCode;
    // 异常处理设置在render内
    public function render(Exception $e){
        // 若异常为Base Exception
        if ($e instanceof BaseException) {
            $this ->code = $e->code;
            $this ->msg = $e->msg;
            $this ->errorCode = $e->errorCode;
        }else{
           
            if(config('app.app_debug')){
                // 如果处于不处于调试模式，将错误对象抛给父类方法，提供人性化界面
                return parent::render($e);
            }
            $this ->code = 500;
            $this ->msg = '服务器异常';
            $this ->errorCode = '999';
        }

        $res = [
            'msg'=>$this->msg,
            'errorCode'=>$this->errorCode
        ];
        // json()的第二个参数 状态码status code
        return json($res,$this->code);
    }
}
