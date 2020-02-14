<?php

namespace app\common\model;

use think\Model;

class Feedback extends Model
{
    protected $autoWriteTimestamp = true;

    // 用户反馈
    public function feedback(){
        $param = request()->param();
        $data = [
            'from_id' => request()->userId,
            'to_id' => 0,
            'data' => $param['data']
        ];
        if (!$this -> create($data)) return TApiException();
        return true;
    }

    // 获取用户反馈列表
    public function feedbacklist(){
        $page = request()->param('page');
        $user_id = request()->userId;
        // 找出from_id或to_id等于指定字段的所有信息
        return $this -> where('from_id',$user_id)->whereOr('to_id',$user_id)->page($page,10)->select();
    }
}
