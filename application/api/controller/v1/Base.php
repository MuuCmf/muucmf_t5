<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Db;
use think\Request;
use Firebase\JWT\JWT;
use app\api\controller\Api;
use app\api\controller\UnauthorizedException;

/**
 * 所有资源类接都必须继承基类控制器
 * 基类控制器提供了基础的验证，包含app_token,请求时间，请求是否合法的一系列的验证
 * 在所有子类中可以调用$this->clientInfo对象访问请求客户端信息，返回为一个数组
 * 在具体资源方法中，不需要再依赖注入，直接调用$this->request返回为请具体信息的一个对象
 */
class Base extends Api
{
    public $key = 'muucmf';

    public $uid;

    public function _initialize()
    {
        parent::_initialize();
        //$this->checkToken();
    }

    /**
     * 验证jwt权限
     *
     * @return     boolean  ( description_of_the_return_value )
     */
    public function checkToken()
    {
        $header = Request::instance()->header();

        if (empty($header['token']) || $header['token'] == 'null'){
            return 'Token不存在,拒绝访问';
        }else{
            $checkJwtToken = $this->verifyJwt($header['token']);
            
            if ($checkJwtToken['status'] == 1001) {
                return true;
            }else{
                return $checkJwtToken['msg'];
            }
        }
    }

    //校验jwt权限API
    protected function verifyJwt($jwt)
    {
        $key = $this->key;
        // JWT::$leeway = 3;
        try {
            $jwtAuth = json_encode(JWT::decode($jwt, $key, array('HS256')));
            $authInfo = json_decode($jwtAuth, true);

            $msg = [];
            if (!empty($authInfo['data']['uid'])) {

                //赋值给$this->uid;
                $this->uid = $authInfo['data']['uid'];

                $msg = [
                    'status' => 1001,
                    'uid' => $authInfo['data']['uid'],
                    'msg' => 'Token验证通过'
                ];
            } else {
                $msg = [
                    'status' => 1002,
                    'msg' => 'Token验证不通过,用户不存在'
                ];
            }
            return $msg;

        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            $msg = [
                'status' => 1002,
                'msg' => 'Token无效'
            ];
            return $msg;
        } catch (\Firebase\JWT\ExpiredException $e) {
            $msg = [
                'status' => 1003,
                'msg' => 'Token过期'
            ];
            return $msg;
        } catch (Exception $e) {
            
            return $e;
        }
    }

    /**
     * 生成JWT
     *
     * @param      <type>  $uid    The uid
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function createJwt($uid)
    {
        $key = $this->key; //jwt的签发密钥，验证token的时候需要用到

        $time = time(); //签发时间

        $expire = $time + 7200; //过期时间

        /*$user_info = query_user([
            'uid',
            'nickname',
            'mobile',
            'email',
            "open_id" => $this->getOpenid($uid),
        ], $uid);*/

        $token = [
            //"uid" => $uid,
            //"user_info" => $user_info,
            "iss" => "https://muucmf.cn",//签发组织
            "aud" => "https://muucmf.cn", //签发作者
            "iat" => $time,
            "nbf" => $time,
            "exp" => $expire,
            "data" => [
                "uid" => $uid,
            ]
        ];

        $jwt = JWT::encode($token, $key);
        //根据ID登陆用户,并记录登陆数据
        model('common/Member')->login($uid);


        return $jwt;
    }

    /**
     * 根据token获取uid
     *
     * @return     integer  The uid.
     */
    public function getUid()
    {
        $header = Request::instance()->header();

        if (empty($header['token']) || $header['token'] == 'null'){
            return 0;
        }else{
            $checkJwtToken = $this->verifyJwt($header['token']);
            if ($checkJwtToken['status'] == 1001) {
                return $checkJwtToken['uid'];
            }
            return 0;
        }
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