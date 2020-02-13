<?php

namespace app\common\model;

use think\Model;

class Post extends Model
{
     // 自动写入时间
     protected $autoWriteTimestamp = true;


        // 关联图片表
        // 建立 Post <--- postImage ---> Image的关联
        // 模型名：Image， 中间表名post_image
    public function images(){
        return $this->belongsToMany('Image','post_image');
    }
    // 关联顶踩表
    public function support(){
        return $this->hasMany("Support");
    }


    // 发布文章
    public function createPost(){
        // 获取所有参数
        $params = request()->param();
        $userModel = new User();
        // 获取用户id
        $user_id=request()->userId;
        // 在主表里获取该条用户记录
        $currentUser = $userModel->get($user_id);
        // 通过一对一关系在userinfo表里找到path
        $path = $currentUser->userinfo->path;
        // 发布文章 截取字符串
        $title = mb_substr($params['text'],0,30);
        // 新建记录
        $post = $this->create([
            'user_id'=>$user_id,
            'title'=>$title,
            'titlepic'=>'',
            'content'=>$params['text'],
            'path'=>$path ? $path : '未知',
            'type'=>0,
            'post_class_id'=>$params['post_class_id'],
            'share_id'=>0,
            'isopen'=>$params['isopen']
        ]);
        // 关联图片
            // 暂无 用户选择图片后后端返回一个数组,将该数组传入即可
        $imglistLength = count($params['imglist']);
        if($imglistLength > 0){
            $ImageModel = new Image();
            $imgidarr = [];
            for ($i=0; $i < $imglistLength; $i++) { 
                // 验证图片是否存在&是否是当前用户上传的
                if ($ImageModel->isImageExist($params['imglist'][$i]['id'],$user_id)) {
                    $imgidarr[] = $params['imglist'][$i]['id'];
                }
            }
            // 发布关联 附加关联的一个中间表数据
            //@param mixed $data — 数据 可以使用数组、关联模型对象 或者 关联对象的主键
            //@param array $pivot — 中间表额外数据
            if(count($imgidarr)>0) $post->images()->attach($imgidarr,['create_time'=>time()]);
        }
        // 返回成功
        return true;
    }


        // 关联用户表 反关联主表 多对一
    public function user(){
        return $this->belongsTo('User');
    }

    // 关联分享  一对多关联
// @param string $model — 模型名
// @param string $foreignKey — 关联外键
// @param string $localKey — 关联主键
    public function share(){
        return $this->belongsTo('Post','share_id','id');
    }

    // 获取文章详情
    public function getPostDetail(){
        // 获取所有参数
        $param = request()->param();
        // 获取更多用户信息
        return $this->with(//把关联的信息返回给这一层
        [
            // $query代表'user'
            'user'=>function($query){
                // 限制回调的字段 减小粒度
                return $query->field('id,username,userpic');
            },
            // 透过中间表
            'images'=>function($query){
                return $query->field('url');
            },
            'share'
        ])->find($param['id']);
    }


        // 根据标题搜索文章
    public function Search(){
        // 获取所有参数
        $param = request()->param();
        return $this->where('title','like','%'.$param['keyword'].'%')->with(
            [
                    'user'=>function($query){
                return $query->field('id,username,userpic');
                },
                    'images'=>function($query){
                return $query->field('url');
                },
                // 如果这篇文章是被分享的，得到原文章的信息
                    'share'
            ])->page($param['page'],10)->select();
    }


        // 关联评论
    public function comment(){
        return $this->hasMany('Comment');
    }


    // 获取评论
    public function getComment(){
        $params = request()->param();
        // 注意要声明comment和user表的关系
        return self::get($params['id'])->comment()->with([
            'user'=>function($query){
                return $query->field('id,username,userpic');
            }
        ])->select();
    }
}
