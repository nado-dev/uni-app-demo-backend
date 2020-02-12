<?php

namespace app\common\controller;

use think\Controller;
use think\Request;

class FileController 
{
        // 上传单文件 size:文件大小限制 ext:文件扩展名
        static public function UploadEvent($files,$size = '2067800',$ext = 'jpg,png,gif',$path = 'uploads')
        {
            // 验证图片尺寸大小
            // 如果验证通过，移动到path指定的目录下，info返回路径
            $info = $files->validate(['size'=>$size,'ext'=>$ext])->move($path);
            return [
                'data'=> $info ? $info->getPathname() : $files->getError(),
                'status'=> $info ? true :false
            ];
        }
}
