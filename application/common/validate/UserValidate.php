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
        'phone'=>'require|mobile'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'phone.require' => '请填写手机号码',
        'phone.mobile' => '手机号码不合法'
    ];

    protected $scene = [
        'sendCode' => ['phone']
    ];
}
