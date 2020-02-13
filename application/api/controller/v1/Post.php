<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;
use think\Controller;
use think\Request;
use app\common\validate\PostValidate;
use app\common\model\Post as PostModel;

class Post extends BaseController
{
    // 发布文章
    public function create(){
        (new PostValidate())->goCheck('create');
        (new PostModel) -> createPost();
        return self::showResCodeWithOutData('发布成功');
    }

        // 文章详情
    public function index()
    {
        // 验证文章id
        (new PostValidate())->goCheck('detail');
        $detail = (new PostModel) -> getPostDetail();
        return self::showResCode('获取成功',['detail'=>$detail]);
    }

        // 文章评论列表
    public function comment(){
        // 验证文章id
        (new PostValidate())->goCheck('detail');
        $list = (new PostModel) -> getComment();
        return self::showResCode('获取成功',['list'=>$list]);
    }
}
