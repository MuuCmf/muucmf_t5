<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Db;
use think\Request;
use app\api\controller\Api;
use think\Response;
use app\api\controller\UnauthorizedException;

/**
 * 所有资源类接都必须继承基类控制器
 * 基类控制器提供了基础的验证，包含app_token,请求时间，请求是否合法的一系列的验证
 * 在所有子类中可以调用$this->clientInfo对象访问请求客户端信息，返回为一个数组
 * 在具体资源方法中，不需要再依赖注入，直接调用$this->request返回为请具体信息的一个对象
 */
class Base extends Api
{
    public function _initialize()
    {
        parent::_initialize();
    }
   /**
     * 通用用户是否登录验证
     */
    public function needLogin(){

        //验证用户授权TOKEN
        //在header中获取token
        $token = Request::instance()->header('token');
        
        if($token){
            $uid = $this->_checkToken($token);//验证用户Token合法性
            if ($uid) {
                return $uid;
            }else{
               return false;
            }
        }

        return false;
    }

    /**
     * { function_description }
     *
     * @param      <type>           $token  The token
     *
     * @return     boolean|integer  ( description_of_the_return_value )
     */
    protected function _checkToken($token){
        //验证用户授权TOKEN
        $uid = $this->getTokenUid($token); //根据token获取uid

        if ($uid || 0 < $uid) {
            //判断是否已经登陆
            if(is_login() == $uid){
                return $uid;
            }else{
                /* 登陆用户 */
                $rs = model('common/Member')->login($uid,1); //登陆
                if ($rs) { //登陆用户
                     return $uid;
                } else {
                     return false;
                }
            }
        }
        return false;
    }

    /**
     * 通过token获取uid
     * 在memberModel模型移植过来，原是cookie机制
     * @param  string $token 通过登陆获取到的token
     * @return int 用户id     
     */
    protected function getTokenUid($token)
    {
        if(!$token){
            return false;
        }
        $token = explode("|", think_decrypt($token));
        $map['uid'] = $token[0];
        $user = Db::name('user_token')->where($map)->find();

        if($user){
        	$token_uid = ($token[1] != $user['token']) ? false : $token[0];

	        $token_uid = $user['time'] - time() >= 3600 * 24 * 7 ? false : $token_uid;//过期时间7天
	        return $token_uid;
        }
        return false;
        
    }

    /**
     * 根据UID获取用户授权Token
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    protected function getToken($uid){
        $map['uid'] = $uid;
        $user_token = Db::name('user_token')->field('token')->where($map)->find();
        $token = think_encrypt($uid.'|'.$user_token['token']);//加密token,每次使用token验证都需要解密操作

        return $token;
    }

    /**
     * 根据uid获取已绑定微信的用户openid
     *
     * @param      <type>  $uid    The uid
     */
    protected function getOpenid($uid)
    {
        $open_id = model('weixin/WeixinOauth')->getOpenid();

        return $open_id;
    }
}