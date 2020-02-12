<?php

namespace app\common\model;


use think\Model;

class TopicClass extends Model
{
        // 获取所有话题分类 返回一个list
    public function getTopicClassList(){
        return $this->field('id,classname')->where('status',1)->select();
    }

        // 关联话题
        // 关联数据库（模型）Topic，一对多
        // 一个话题分类(topicclass)下有多个话题(topic)
    public function topic(){
        return $this->hasMany('Topic');
    }
    // 获取指定话题分类下的话题（分页）
    public function getTopic(){
        // 获取所有参数
        $param = request()->param();
        // 静态方法：get查询单个记录 支持关联预载入
        // topic() :获取该分类的topic字段
        // page()分页: 第一个参数指定当前页数， 第二个页数最大页数 
        // select：选择
        return self::get($param['id'])->topic()->page($param['page'],10)->select();
    }
}
