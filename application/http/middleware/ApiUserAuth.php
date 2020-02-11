<?php

namespace app\http\middleware;

class ApiUserAuth
{
    public function handle($request, \Closure $next)
    {
        // 获取头部信息 token
        $param = $request->header(); 
        // 不含token
        if (!array_key_exists('token',$param)) 
            TApiException('非法token，禁止操作', 20003, 200);
        // 当前用户token是否存在（是否登录）,存在则从缓存中提取信息
        $token = $param['token'];
        $user = \Cache::get($token);
        // 验证失败（未登录或已过期）
        if(!$user) 
            TApiException('非法token，请重新登录，未登录或已过期', 20003, 200);
        // 将token和userid这类常用参数放在request中
        // 关键
        $request->userToken = $token;
        $request->userId = array_key_exists('type',$user) ? $user['user_id'] : $user['id'];
        $request->userTokenUserInfo = $user; 
        return $next($request);
    }
}
