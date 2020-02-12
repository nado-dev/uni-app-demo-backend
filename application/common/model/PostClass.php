<?php

namespace app\common\model;

use think\Model;
use app\common\validate\TopicClassValidate;
use app\common\model\TopicClass as PostClassModel;
class PostClass extends Model
{
        // 获取所有文章分类
        // field 限制查询数据表的字段
        // where 查询出状态为1的记录 
        // select 返回的结果包含了上述field的两个字段
    public function getPostClassList(){
        return $this->field('id,classname')->where('status',1)->select();
    }

        // 关联文章模型
    public function post(){
        return $this->hasMany('Post');
    }

    // 获取指定分类下的文章（分页）
    public function getPost(){
        // 获取所有参数
        $param = request()->param();
        return self::get($param['id'])->post()->with(
            [
                    'user'=>function($query){
                    return $query->field('id,username,userpic');
                },
                    'images'=>function($query)
                {
                    return $query->field('url');
                },
                    'share'
            ])->page($param['page'],10)->select();
    }
}
