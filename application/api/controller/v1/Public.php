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

/**
 * 公共常用功能接口
 */
class Public extends Base
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
        //根据action 参数判断操作类型
        $action = input('action','','text');
        switch($action){
            case 'send_verify'://发送验证码
                
                $uid = input('uid',get_uid(),'intval');
                $aAccount = $cUsername = input('post.account', '', 'text');
                $aType = input('post.type', '', 'text');
                $aType = $aType == 'mobile' ? 'mobile' : 'email';

                if (!check_reg_type($aType)) {
                    $str = $aType == 'mobile' ? lang('_PHONE_') : lang('_EMAIL_');

                    return $this->sendError($str . lang('_ERROR_OPTIONS_CLOSED_').lang('_EXCLAMATION_'));  
                }

                if (empty($aAccount)) {
                    
                    return $this->sendError(lang('_ERROR_ACCOUNT_CANNOT_EMPTY_'));  
 
                }

                check_username($cUsername, $cEmail, $cMobile);
                $time = time();

                if($aType == 'mobile'){
                    $resend_time =  modC('SMS_RESEND','60','USERCONFIG');
                    if($time <= session('verify_time')+$resend_time ){
                        $result = lang('_ERROR_WAIT_1_').($resend_time-($time-session('verify_time'))).lang('_ERROR_WAIT_2_');
                        return $this->sendError($result);
                    }
                }

                if ($aType == 'email' && empty($cEmail)) {

                    return $this->sendError(lang('_ERROR__EMAIL_'));  
                }

                if ($aType == 'mobile' && empty($cMobile)) {

                    return $this->sendError(lang('_ERROR_PHONE_'));  
                }

                $checkIsExist = UCenterMember()->where(array($aType => $aAccount))->find();
                if ($checkIsExist) {
                    $str = $aType == 'mobile' ? lang('_PHONE_') : lang('_EMAIL_');
                    $result = lang('_ERROR_USED_1_') . $str . lang('_ERROR_USED_2_').lang('_EXCLAMATION_');

                    return $this->sendError($result);  
                }

                $verify = model('Verify')->addVerify($aAccount, $aType, $uid);
                if (!$verify) {
                    $result = lang('_ERROR_FAIL_SEND_').lang('_EXCLAMATION_');
                    return $this->sendError($result);
                }

                $res = doSendVerify($aAccount, $verify, $aType);
                if ($res === true) {
                    if($aType == 'mobile'){
                        session('verify_time',$time);
                    }
                    return $this->sendSuccess('发送成功');
                    
                } else {

                    return $this->sendError(lang('_ERROR_SUCCESS_SEND_'));  
                }
            break;  
        } 

        return $this->sendError('无操作参数');  
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
