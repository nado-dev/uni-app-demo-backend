<?php

namespace app\common\validate;

use think\Validate;

class PostValidate extends BaseValidate
{
    protected $rule = [
        'text'=>'require',
        'imglist'=>'require|array',
        // 可见权限
        'isopen'=>'require|in:0,1',
        'topic_id'=>'require|integer|>:0|isTopicExist',
        'post_class_id'=>'require|integer|>:0|isPostClassExist',
        'id'=>'require|integer|>:0',
    ];
    
    protected $scene = [
        // 发布
        'create'=>['text','imglist','token','isopen','topic_id','post_class_id'],
        // 获取消息
        'detail'=>['id']
    ];

    protected $message=[
        
    ];
}
