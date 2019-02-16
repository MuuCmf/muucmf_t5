<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Request;
use app\api\controller\Api;
use think\Response;
use app\api\controller\UnauthorizedException;
use app\api\controller\v1\Base;

/**
 * 所有资源类接都必须继承基类控制器
 * 基类控制器提供了基础的验证，包含app_token,请求时间，请求是否合法的一系列的验证
 * 在所有子类中可以调用$this->clientInfo对象访问请求客户端信息，返回为一个数组
 * 在具体资源方法中，不需要再依赖注入，直接调用$this->request返回为请具体信息的一个对象
 */
class User extends Base
{   
    /**
     * 允许访问的方式列表，资源数组如果没有对应的方式列表，请不要把该方法写上，如user这个资源，客户端没有delete操作
     */
    public $restMethodList = 'get|post|put';
    public $apiAuth = false;

    
    /**
     * restful没有任何参数
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->sendError('uid参数错误');  
    }

    /**
     * post方式
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save()
    {
        
        $action = input('action','save','text');
        switch($action){
            case 'save':
                //需要登陆
                if(!$this->needLogin()){
                    return $this->sendError('Need login');
                }
                echo 'save';
                //dump($this->request);
                dump($this->clientInfo);
            break;

            case 'login':
                $aUsername = $username = input('post.account', '', 'text');
                $aPassword = input('post.password', '', 'text');
                check_username($aUsername, $email, $mobile, $aUnType);

                //dump(input(''));exit;
                //根据用户账号密码获取用户ID或返回错误码
                $code = $uid = model('ucenter/UcenterMember')->login($username, $aPassword, $aUnType);
                
                if($code > 0){
                    //根据ID登陆用户
                    $rs = model('common/Member')->login($uid, 1); //登陆
                    if ($rs) {
                        $token = $this->getToken($uid);
                        $user_info = query_user(array('uid','nickname','sex','avatar32','mobile','email','title','last_login_ip','last_login_time',), $uid);
                        $result['msg'] = '登陆成功';
                        $result['token']=$token; //用户持久登录token
                        $result['data'] = $user_info;
                    }
                }else{
                    $msg = model('common/Member')->showRegError($code);
                    return $this->sendError($msg);
                }
                //判断是否登陆成功
                return $this->sendSuccess('success',$result);
            break;
        }
        
    }

    /**
     * get方式
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {   
        $aUid = $id;
        if($aUid){
            $map['uid'] = $aUid;
            $userData=model('member')->where($map)->find();
            if($userData){
                $data = query_user([
                    'uid',
                    'nickname',
                    'sex',
                    'birthday',
                    'reg_ip',
                    'last_login_ip',
                    'last_login_time',
                    'avatar32',
                    'avatar128',
                    'mobile',
                    'email',
                    'username',
                    'title',
                    'signature',
                    'score',
                    'score1',
                    'score2',
                    'score3',
                    'score4'
                ], $aUid);
                
                return $this->sendSuccess('success',$data);
            }else{
                return $this->sendError();  
            }
            return $this->sendError('uid参数错误');  
        }
    }

    /**
     * PUT方式
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update()
    {
        return 'update';
    }

    /**
     * delete方式
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete()
    {
        return 'delete';
    }

    /**
     * 获取除资源方法外的方法
     */
    public function fans($id)
    {
        return $id;
    }

    public function login()
    {
        
    }
}
