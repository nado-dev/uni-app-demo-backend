<?php

namespace app\common\model;

use think\Model;

class Update extends Model
{
    protected $autoWriteTimestamp = true;

    // 检测更新
    public function appUpdate(){
        $version = request()->param('ver');
        // 在数据表中按时间排序 返回最新版本的版本号
        $res = $this->where('status',1)->order('create_time','desc')->find();
        // 无记录
        if (!$res) TApiException('暂无更新版本');
        // 当前为最新版本
        if ( $res['version'] == $version ) TApiException('暂无更新版本');
        return $res;
    }
}
