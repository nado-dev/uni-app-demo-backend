<?php

namespace app\common\validate;

use think\Validate;

class TopicClassValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id'=>'require|integer|>:0', //>:0 大于0 integer 整型
        'page'=>'require|integer|>:0',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'id.require' => '话题id为空，请指定id',
        'id.integer' => '话题id必须为整型',
        'id.>:0'     => 'id必须为正数',
        'page.require' => '页数为空，请指定页数',
        'page.integer' => '页数必须为整型',
        'page.>:0'     => '页数必须为正数'
    ];
}
