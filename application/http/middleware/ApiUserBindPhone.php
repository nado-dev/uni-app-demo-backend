<?php

namespace app\http\middleware;
use app\common\model\User;

class ApiUserBindPhone
{
    public function handle($request, \Closure $next)
    {   
        if ($request->userId < 1) {
            TApiException('请先绑定手机', 20008);
        }
        return $next($request);
    }
}
