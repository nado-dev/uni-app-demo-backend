<?php
namespace app\common\validate;
use think\Validate;
use app\lib\exception\BaseException;

class BaseValidate extends Validate
{
    public function goCheck($scene = false){
         // 获取请求传递过来的所有参数
         $params = request()->param();
         // 开始验证
         $check = $scene ?
                $this->scene($scene)->check($params):
                $this->check($params);
         if (!$check) {
            throw new BaseException(['msg'=>$this->getError(),'errorCode'=>10000,'code'=>400]);
         }
         return true;
    }

    protected function isPerfectCode($value, $rule='', $data='', $field= ''){ // feild = 'code'
        // 从缓存中取出当前手机号对应的存入验证码
        $beforeCode = cache($data['phone']);
        // 已过期
        if(! $beforeCode) return '请重新获取验证码';
        if($value != $beforeCode) return "验证码错误,应该是".$beforeCode."而输入的是".$value;
        return true;
    }
}
