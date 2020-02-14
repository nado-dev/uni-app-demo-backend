<?php

namespace app\common\model;

use think\Model;
use app\lib\exception\BaseException;
use think\facade\Cache;
use app\common\controller\AliSMSController;
use app\common\validate\UserValidate;

class User extends Model
{
    // 自动写入时间
    protected $autoWriteTimestamp = true;


    // 发送验证码
    public function sendCode(){
    // 获取用户提交手机号码
        $phone = request()->param('phone');
        // 判断是否已经发送过  查找缓存
        if(Cache::get($phone)) 
            throw new BaseException(['code'=>200,'msg'=>'你操作得太快了','errorCode'=>30001]);
        // 生成4位验证码
        $code = random_int(1000,9999);
        // 判断是否开启验证码功能 模拟验证开关
        if(!config('api.alisms.isopen')){
            Cache::set($phone,$code,config('api.alisms.expire'));
            throw new BaseException(['code'=>200,'msg'=>'验证码：'.$code,'errorCode'=>30005]);
        }
        //模拟 第一个参数 key 第二个参数 value 第三个参数 有效时间
        // Cache::set($phone,$code,60);
        // throw new BaseException(['code'=>200,'msg'=>'验证码：'.$code,'errorCode'=>30005]);
        // 发送验证码
        $res = AlismsController::SendSMS($phone,$code);
        //发送成功 写入缓存
        if($res['Code']=='OK') 
            return Cache::set($phone,$code,config('api.alisms.expire'));
        // 无效号码
        if($res['Code']=='isv.MOBILE_NUMBER_ILLEGAL') 
            throw new BaseException(['code'=>200,'msg'=>'无效号码','errorCode'=>30002]);
        // 触发日限制
        if($res['Code']=='isv.DAY_LIMIT_CONTROL') 
            throw new BaseException(['code'=>200,'msg'=>'今日你已经发送超过限制，改日再来','errorCode'=>30003]);
        
        // 发送失败
        throw new BaseException(['code'=>200,'msg'=>'发送失败','errorCode'=>30004]);

    }


    // 手机登录（补充前面课程）
public function phoneLogin(){
    // 获取所有参数
    $param = request()->param();
    // 验证用户是否存在
    $user = $this->isExist(['phone'=>$param['phone']]);
    // 用户不存在，直接注册
    if(!$user){
        // 用户主表
        $user = self::create([
            'username'=>$param['phone'],
            'phone'=>$param['phone'],
            'password'=>password_hash($param['phone'],PASSWORD_DEFAULT)
        ]);
        // 在用户信息表创建对应的记录（用户存放用户其他信息）
        $user->userinfo()->create([ 'user_id'=>$user->id ]);
        $user->logintype = 'phone';
        return $this->CreateSaveToken($user->toArray());
    }
    // 用户是否被禁用
    $this->checkStatus($user->toArray());
    // 登录成功，返回token
    return $this->CreateSaveToken($user->toArray());
}
    

        // 用户是否被禁用（在前面课程基础上扩展）
    // 用户是否被禁用（在前面课程的基础上扩充）
    public function checkStatus($arr,$isReget = false){
        $status = 1;
        if ($isReget) {
            // 账号密码登录
            $userid = array_key_exists('user_id',$arr)?$arr['user_id']:$arr['id'];
             // 判断第三方登录是否绑定了手机号码
            if ($userid < 1) return $arr;
            $user = $this->find($userid)->toArray();
            $status = $user['status'];
        }else{
            $status = $arr['status'];
        }
        if($status==0) throw new BaseException(['code'=>200,'msg'=>'该用户已被禁用','errorCode'=>20001]);
        return $arr;
    }


    // 绑定用户信息表 一对一关系 关联了模型 Userinfo
    // 模型 ----->  数据表
    // 返回一个与数据表的链接的实例
    public function userinfo(){
        return $this->hasOne('Userinfo');
    }


    // 绑定第三方登录 一对多关系
    public function userbind(){
        return $this->hasMany('UserBind');
    }


    public function isExist($arr=[]){
        if(!is_array($arr)) return false;
        if (array_key_exists('phone',$arr)) { // 手机号码
            $user = $this->where('phone',$arr['phone'])->find();
            if ($user) 
                $user->logintype = 'phone';
            return $user;
        }
        // 用户id
        if (array_key_exists('id',$arr)) { // 用户名
            return $this->where('id',$arr['id'])->find();
        }
        if (array_key_exists('email',$arr)) { // 邮箱
            $user = $this->where('email',$arr['email'])->find();
            if ($user) $user->logintype = 'email';
            return $user;
        }
        if (array_key_exists('username',$arr)) { // 用户名
            $user = $this->where('username',$arr['username'])->find();
            if ($user) $user->logintype = 'username';
            return $user;
        }
        // 第三方参数
        if (array_key_exists('provider',$arr)) {
            $where = [
                'type'=>$arr['provider'],
                'openid'=>$arr['openid']
            ];
            $user = $this->userbind()->where($where)->find();
            if ($user) $user->logintype = $arr['provider'];
            return $user;
        }
        return false;
    }
    


    // 生成并保存token
    public function CreateSaveToken($arr=[]){
        // 生成token 不断加密混淆 随机性
        $token = sha1(md5(uniqid(md5(microtime(true)),true)));
        // 添加token值
        $arr['token'] = $token;
        // 登录过期时间
        $expire =array_key_exists('expires_in',$arr) ? $arr['expires_in'] : config('api.token_expire');
        // 保存到缓存中 key:token val expire
        if (!Cache::set($token,$arr,$expire)) 
            throw new BaseException();
        // 返回token
        return $token;
    }


    // 账号登录
    public function login(){
        // 获取所有参数
        $param = request()->param();
        // 验证用户是否存在 filterUserData 正则验证合法性
        $user = $this->isExist($this->filterUserData($param['username']));
        // 用户不存在
        if(!$user) throw new BaseException(['code'=>200,'msg'=>'昵称/邮箱/手机号错误','errorCode'=>20000]);
        // 用户是否被禁用
        $this->checkStatus($user->toArray());
        // 验证密码
        $this->checkPassword($param['password'],$user->password);
        // 登录成功 生成token，进行缓存，返回客户端
        return $this->CreateSaveToken($user->toArray());
    }


    // 验证用户名是什么格式，昵称/邮箱/手机号
    public function filterUserData($data){
        $arr=[];
        // 验证是否是手机号码
        if(preg_match('^1(3|4|5|7|8)[0-9]\d{8}$^', $data)){
            $arr['phone']=$data; 
            return $arr;
        }
        // 验证是否是邮箱
        if(preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', $data)){
            $arr['email']=$data; 
            return $arr;
        }
        $arr['username']=$data; 
        return $arr;
    }   


        // 验证密码
    public function checkPassword($password,$hash){
        if (!$hash) throw new BaseException(['code'=>200,'msg'=>'密码不存在','errorCode'=>20002]);
        // 密码错误  指纹验证 输入的密码与数据库指纹验证
        if(!password_verify($password,$hash)) throw new BaseException(['code'=>200,'msg'=>'密码错误','errorCode'=>20003]);
        return true;
    }


       // 第三方登录（补充前面课程）
public function otherlogin(){
    // 获取所有参数
    $param = request()->param();
    // 解密过程（待添加）
    // 验证用户是否存在
    $user = $this->isExist(['provider'=>$param['provider'],'openid'=>$param['openid']]);
    // 用户不存在，创建用户
    $arr = [];
    if (!$user) {
        $user = $this->userbind()->create([
            'type'=>$param['provider'],
            'openid'=>$param['openid'],
            'nickname'=>$param['nickName'],
            'avatarurl'=>$param['avatarUrl'],
        ]);
        $arr = $user->toArray();
        $arr['expires_in'] = $param['expires_in']; 
        $arr['logintype'] = $param['provider']; 
        return $this->CreateSaveToken($arr);
    }
    // 用户是否被禁用
    $arr = $this->checkStatus($user->toArray(),true);
    // 登录成功，返回token
    $arr['expires_in'] = $param['expires_in']; 
    return $this->CreateSaveToken($arr);
}


    // 验证第三方登录是否绑定手机 @var Array
    public function OtherLoginIsBindPhone($user){
        // 验证是否是第三方登录
        if(array_key_exists('type',$user)){
            if($user['user_id']<1){
                TApiException('请先绑定手机！',20008);
            }
            return $user['user_id'];
        }
        // 账号密码登录
        return $user['id'];
    }


    // 退出登录
    public function logout(){
        // 释放缓存 （出栈）
        if (!Cache::pull(request()->userToken)) 
        TApiException('你已经退出了',30006);
        return true;
    }    


        // 关联文章 一对多
    public function post(){
        return $this->hasMany('Post');
    }



    // 获取指定用户下文章
    public function getPostList(){
        $params = request()->param();
        $user = $this->get($params['id']);
        if (!$user) TApiException('该用户不存在',10000);

        return $user->post()->with(
            [
                'user'=>function($query){
                    return $query->field('id,username,userpic');
                },
                'images'=>function($query){
                    return $query->field('url');
                },
                'share'])->where('isopen',1)->page($params['page'],10)->select();
                // where 权限控制
    }


        // 获取指定用户下所有文章
    public function getAllPostList(){
        $params = request()->param();
        // 获取用户id 中间件已经获取了
        $user_id = request()->userId;
        return $this->get($user_id)->post()->with(
            [
            'user'=>function($query){
                return $query->field('id,username,userpic');
            },
            'images'=>function($query){
                return $query->field('url');
            },
            'share'])->page($params['page'],10)->select();
            // 遇上一个相比无权限控制
    }


        // 搜索用户
    public function Search(){
        // 获取所有参数
        $param = request()->param();
        // 必须隐藏密码字段
        return $this->where('username','like','%'.$param['keyword'].'%')->page($param['page'],10)->hidden(['password'])->select();
    }




        // 验证当前绑定类型是否冲突
    public function checkBindType($current,$bindtype){
        // 当前绑定类型
        if($bindtype == $current) TApiException('不可以改变已绑定手机，绑定类型冲突');
        return true;
    }



        // 绑定手机
    public function bindphone(){
        // 获取所有参数
        $params = request()->param();
        // 中间件
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType,'phone');
        // 查询该手机是否绑定了其他用户 主表里的用户信息记录
        $binduser = $this->isExist(['phone'=>$params['phone']]);
        // 存在 该手机用户存在
        if ($binduser) {
            // 账号邮箱登录
            if ($currentLoginType == 'username' || $currentLoginType == 'email') 
                TApiException('已被绑定',20006,200);
            // 第三方登录  该手机用户已经绑定了这个第三方平台
            if ($binduser->userbind()->where('type',$currentLoginType)->find()) 
                TApiException('已被绑定',20006,200);
            // 直接修改 该手机用户已经未绑定这个第三方平台
            // 用主表里的id(user_id)修改第三方表的user_id
            $userbind = $this->userbind()->find($currentUserInfo['id']);//用第三方登录的id找到这条记录
            $userbind->user_id = $binduser->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $binduser->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        // 不存在
        // 账号邮箱登录
        if ($currentLoginType == 'username' || $currentLoginType == 'email'){
            $user = $this->save([
                'phone'=>$params['phone']
            ],['id'=>$currentUserId]);
            // 更新缓存
            $currentUserInfo['phone'] = $params['phone'];
            Cache::set($currentUserInfo['token'],$currentUserInfo,config('api.token_expire'));
            return true;
        }
        // 第三方登录 已登录过 但未绑定手机
        if (!$currentUserId) {
            // 在user表创建账号
            $user = $this->create([
                'username'=>$params['phone'],
                'phone'=>$params['phone'],
            ]);
            // 绑定
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $user->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $user->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        // 直接修改 已登录过 已经绑定手机 相当于换绑
        if($this->save([
            'phone'=>$params['phone']
        ],['id'=>$currentUserId])) return true;
        TApiException();
    }


        // 绑定邮箱
    public function bindemail(){
        // 获取所有参数
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType,'email');
        // 查询该yx是否绑定了其他用户
        $binduser = $this->isExist(['email'=>$params['email']]);
        // 存在
        if ($binduser) {
            // 账号手机登录
            if ($currentLoginType == 'username' || $currentLoginType == 'phone') TApiException('已被绑定',20006,200);
            // 第三方登录
            if ($binduser->userbind()->where('type',$currentLoginType)->find()) TApiException('已被绑定',20006,200);
            // 直接修改
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $binduser->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $binduser->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        // 不存在
        // 账号手机登录
        if ($currentLoginType == 'username' || $currentLoginType == 'phone'){
            $user = $this->save([
                'email'=>$params['email']
            ],['id'=>$currentUserId]);
            // 更新缓存
            $currentUserInfo['email'] = $params['email'];
            Cache::set($currentUserInfo['token'],$currentUserInfo,config('api.token_expire'));
            return true;
        }
        // 第三方登录
        if (!$currentUserId) {
            // 在user表创建账号
            $user = $this->create([
                'username'=>$params['email'],
                'email'=>$params['email'],
            ]);
            // 绑定
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $user->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $user->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        // 直接修改  user表里的Email换绑
        if($this->save([
            'email'=>$params['email']
        ],['id'=>$currentUserId])) return true;
        TApiException();
    }



        // 绑定第三方登录
    public function bindother(){
        // 获取所有参数
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType,$params['provider']);
        // 查询是否存在该用户
        $binduser = $this->isExist(['provider'=>$params['provider'],'openid'=>$params['openid']]);
        // 存在
        if ($binduser) {
            // 该第三方登录是否绑定了手机
            // 已经绑定 抛出异常
            if ($binduser->user_id) TApiException('已被绑定',20006,200);
            // else uid=0
            $binduser->user_id = $currentUserId;
            return $binduser->save();
        }
        // 不存在
        return $this->userbind()->create([
            'type'=>$params['provider'],
            'openid'=>$params['openid'],
            'nickname'=>$params['nickName'],
            'avatarurl'=>$params['avatarUrl'],
            'user_id'=>$currentUserId
        ]);
    }
    

        //  修改头像
    public function editUserpic(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid=request()->userId;
        // @useris:userid @[field]：指定request参数中要上传的字段
        $image = (new Image())->upload($userid,'userpic');
        // 修改用户头像
        $user = self::get($userid);
        $user->userpic = getFileUrl($image->url);
        if($user->save()) return true;
        TApiException("图片存储失败",30000,400);
    }

        // 修改资料
    public function editUserinfo(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid=request()->userId;
        // 修改昵称
        $user = $this->get($userid);
        $user->username = $params['name'];
        $user->save();
        // 修改用户信息表 一对一关系找到userinfo表记录 并修改
        $userinfo = $user->userinfo()->find();
        $userinfo->sex = $params['sex'];
        $userinfo->qg = $params['qg'];
        $userinfo->job = $params['job'];
        $userinfo->birthday = $params['birthday'];
        $userinfo->path = $params['path'];
        $userinfo->save();
        return true;
    }



        // 修改密码
    public function repassword(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $user = self::get($userid);
        // 手机注册的用户并没有原密码,直接修改即可
        if ($user['password']) {
            // 判断旧密码是否正确
            $this->checkPassword($params['oldpassword'],$user['password']);
        }
        // 修改密码
        $newpassword = password_hash($params['newpassword'],PASSWORD_DEFAULT);
        $res = $this->save([
            'password'=>$newpassword
        ],['id'=>$userid]);
        if (!$res) TApiException('修改密码失败',20009,200);
        $user['password'] = $newpassword;
        // 更新缓存信息
        Cache::set(request()->Token,$user,config('api.token_expire'));
    }


    // 关联关注
    public function withfollow(){
        return $this->hasMany('Follow','user_id');
    }
    // 关注用户
    public function ToFollow(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $user_id = request()->userId;
        $follow_id = $params['follow_id'];
        // 不能关注自己
        if($user_id == $follow_id) TApiException('非法操作',10000,200);
        // 获取到当前用户的关注模型
        $followModel = $this->get($user_id)->withfollow();
        // 查询记录是否存在
        $follow = $followModel->where('follow_id',$follow_id)->find();
        if($follow) TApiException('已经关注过了',10000,200);
        $followModel->create([
            'user_id'=>$user_id,
            'follow_id'=>$follow_id
        ]);
        return true;
    }



    // 取消关注
    public function ToUnFollow(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $user_id = request()->userId;
        $follow_id = $params['follow_id'];
        // 不能取消关注自己
        if($user_id == $follow_id) TApiException('非法操作',10000,200);
        $followModel = $this->get($user_id)->withfollow();
        $follow = $followModel->where('follow_id',$follow_id)->find();
        if(!$follow) TApiException('暂未关注',10000,200);
        $follow->delete();
    }


    // 获取互关列表
    public function getFriendsList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $page = $params['page'];
        // 模型关联无法满足需求时可以使用Db类
        $follows = \Db::table('user')->where('id','IN', function($query) use($userid)
        {
            // 找出所有关注我的人的用户id
            $query->table('follow')
                ->where('user_id', 'IN', function ($query) use($userid){
                    //  找出所有我关注的人的id
                    $query->table('follow')->where('user_id', $userid)->field('follow_id');
                })->where('follow_id',$userid)
                ->field('user_id');
        })->field('id,username,userpic')->page($page,10)->select();
        return $follows;
    }


    // 关联粉丝列表
    // 多对多：粉丝与用户网状关系
    /**
     *@param string $model — 模型名
     *@param string $table — 中间表名
     *@param string $foreignKey — 关联外键
     *@param string $localKey — 当前模型关联键
     *
     * 判定技巧 在中间表中，通过传来的主表主键  查找关联的外键（参数3），在中间表中对应该表主键（当前模型关联键，参数4），
     */
    public function fens(){
        return $this->belongsToMany('User','Follow','user_id','follow_id');
    }
   
   
    // 获取当前用户粉丝列表
    public function getFensList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $fens = $this->get($userid)->fens()->page($params['page'],10)->select()->toArray();
        return $this->filterReturn($fens);
    }



    // 限制关注和粉丝返回字段
    public function filterReturn($param = []){
        $arr = [];
        $length = count($param);
        for ($i=0; $i < $length; $i++) { 
            $arr[] = [
                'id'=>$param[$i]['id'],
                'username'=>$param[$i]['username'],
                'userpic'=>$param[$i]['userpic'],
            ];
        }
        return $arr;
    }


    // 关联关注列表
    // 关联关系颠倒
     /**
     *@param string $model — 模型名
     *@param string $table — 中间表名
     *@param string $foreignKey — 关联外键
     *@param string $localKey — 当前模型关联键 主键
     * 判定技巧 在中间表中，通过传来的主表主键  查找关联的外键（参数3），在中间表中对应该表主键（当前模型关联键，参数4），
     */
    public function follows(){
        return $this->belongsToMany('User','Follow','follow_id','user_id');
    }


    // 获取当前用户关注列表
    public function getFollowsList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $follows = $this->get($userid)->follows()->page($params['page'],10)->select()->toArray();
        return $this->filterReturn($follows);
    }
}

