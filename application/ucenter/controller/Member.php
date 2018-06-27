<?php
/**
 * 放置用户登陆注册
 */
namespace app\ucenter\controller;

use think\Controller;
use think\Db;
use app\ucenter\model\UcenterMember;
use app\common\Model\FollowModel;

//require_once APP_PATH . 'user/config.php';

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class Member extends Controller
{

    /**
     * register  注册页面
     */
    public function register()
    {
        //获取参数
        $aUsername = $username = input('post.username', '', 'text');
        $aNickname = input('post.nickname', '', 'text');
        $aPassword = input('post.password', '', 'text');
        $aVerify = input('post.verify', '', 'text');
        $aRegVerify = input('post.reg_verify', '', 'text');
        $aRegType = input('post.reg_type', '', 'text');
        $aStep = input('get.step', 'start', 'text');
        $aRole = input('post.role', 0, 'intval');

        if (!modC('REG_SWITCH', '', 'USERCONFIG')) {
            $this->error(lang('_ERROR_REGISTER_CLOSED_'));
        }
        if (request()->isPost()) {
            //注册用户
            $return = check_action_limit('reg', 'ucenter_member', 1, 1, true);
            
            if ($return && !$return['state']) {
                $this->error($return['info'], $return['url']);
            }

            /* 检测验证码 */
            if (check_verify_open('reg')) {
                if (!check_verify($aVerify)) {
                    $this->error(lang('_ERROR_VERIFY_CODE_').lang('_PERIOD_'));
                }
            }
            if (!$aRole) {
                $this->error(lang('_ERROR_ROLE_SELECT_').lang('_PERIOD_'));
            }

            if (($aRegType == 'mobile' && modC('MOBILE_VERIFY_TYPE', 0, 'USERCONFIG') == 1) || (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 2 && $aRegType == 'email')) {
                if (!model('Verify')->checkVerify($aUsername, $aRegType, $aRegVerify, 0)) {
                    $str = $aRegType == 'mobile' ? lang('_PHONE_') : lang('_EMAIL_');
                    $this->error($str . lang('_FAIL_VERIFY_'));
                }
            }
            $aUnType = 0;
            //获取注册类型
            check_username($aUsername, $email, $mobile, $aUnType);
            if ($aRegType == 'email' && $aUnType != 2) {
                $this->error(lang('_ERROR_EMAIL_FORMAT_'));
            }
            if ($aRegType == 'mobile' && $aUnType != 3) {
                $this->error(lang('_ERROR_PHONE_FORMAT_'));
            }
            if (!check_reg_type($aUnType)) {
                $this->error(lang('_ERROR_REGISTER_NOT_OPENED_').lang('_PERIOD_'));
            }
            //获取和判断邀请码
            $aCode = input('post.code', '', 'text');
            if (!$this->checkInviteCode($aCode)) {
                $this->error(lang('_ERROR_INV_ILLEGAL_').lang('_EXCLAMATION_'));
            }

            /* 注册用户 */

            $ucenterMemberModel = new UcenterMember;
            $uid =$ucenterMemberModel->register($aUsername, $aNickname, $aPassword, $email, $mobile, $aUnType);
            if (0 < $uid) { //注册成功
                //echo $uid;exit;
                $this->initInviteUser($uid, $aCode, $aRole);

                model('Member')->initRoleUser($aRole, $uid); //初始化角色用户

                if (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 1 && $aUnType == 2) {
                    set_user_status($uid, 3);
                    $verify = model('Verify')->addVerify($email, 'email', $uid);
                    $res = $this->sendActivateEmail($email, $verify, $uid); //发送激活邮件
                    // $this->success('注册成功，请登录邮箱进行激活');
                }

                $uid = $ucenterMemberModel->login($username, $aPassword, $aUnType); //通过账号密码取到uid

                $res = model('Member')->login($uid, false, $aRole); //登陆
                //dump($res);exit;
                //未设置启用任何注册后步骤操作就跳过
                $step_config = modC('REG_STEP','','USERCONFIG');
                if($step_config){
                   $step_config = json_decode($step_config,ture); 
                }
                
                if(empty($step_config[1]['items'])){
                    Db::name('UserRole')->where(array('uid' => $uid))->setField('step', 'finish');
                    $step_url = Url('index/Index/index');
                }else{
                    //构建注册步骤URL
                    $step_url = Url('Ucenter/member/step', array('step' => get_next_step('start')));
                }
                $this->success('注册成功', $step_url);
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
            //显示注册表单
            if (is_login()) {
                redirect(Url('index/Index/index'));
            }
            $regType = $this->checkRegisterType();
            $aType = input('get.type', '', 'op_t');
            $regSwitch = modC('REG_SWITCH', '', 'USERCONFIG');
            $regSwitch = explode(',', $regSwitch);
            $this->assign('regSwitch', $regSwitch);
            $this->assign('step', $aStep);
            $this->assign('type', $aType == '' ? 'username' : $aType);
            return $this->fetch();
        }
    }

    public function step()
    {
        $user = session('user_auth');
        $aStep = I('get.step', '', 'text');
        $aUid = $user['uid'];
        $aRoleId = $user['role_id'];

        if (empty($aUid)) {
            $this->error(lang('_ERROR_PARAM_'));
        }
        $userRoleModel = D('UserRole');
        $map['uid'] = $aUid;
        $map['role_id'] = $aRoleId;
        $step = $userRoleModel->where($map)->getField('step');

        if (get_next_step($step) != $aStep) {
            $aStep = check_step($step);
            $_GET['step'] = $aStep;
            $userRoleModel->where($map)->setField('step', $aStep);
        }
        $userRoleModel->where($map)->setField('step', $aStep);
        if ($aStep == 'finish') {
            D('Member')->login($aUid, false, $aRoleId);
        }
        //取得站点LOGO
        $logo = get_cover(modC('LOGO',0,'Config'),'path');
        $logo = $logo?$logo:__ROOT__.'/Public/images/logo.png';
        //取得用户资料
        $user_info =  query_user(array('uid', 'nickname', 'email','avatar64'), $aUid);

        $this->assign('user_info',$user_info);
        $this->assign('logo',$logo);
        $this->assign('step', $aStep);
        $this->display('register');
    }

    public function inCode()
    {
        if (IS_POST) {
            $aType = I('get.type', '', 'op_t');
            $aCode = I('post.code', '', 'op_t');
            $result['status'] = 0;
            if (!mb_strlen($aCode)) {
                $result['info'] = lang('_INFO_PLEASE_INPUT_').lang('_EXCLAMATION_');
                $this->ajaxReturn($result);
            }
            $invite = D('Ucenter/Invite')->getByCode($aCode);
            if ($invite) {
                if ($invite['end_time'] > time()) {
                    $result['status'] = 1;
                    $result['url'] = U('Ucenter/Member/register', array('code' => $aCode, 'type' => $aType));
                } else {
                    $result['info'] = lang('_INFO_INV_CODE_EXPIRED_');
                }
            } else {
                $result['info'] = lang('_INFO_NOT_EXIST_');
            }
            $this->ajaxReturn($result);
        } else {
            $this->display();
        }
    }

    public function upRole()
    {
        $aRoleId = input('role_id', 0, 'intval');
        if (IS_POST) {
            $uid = is_login();
            $result['status'] = 0;
            if ($uid > 0 && $aRoleId != get_login_role()) {
                $aCode = input('post.code', '', 'text');
                if (!mb_strlen($aCode)) {
                    $result['info'] = lang('_INFO_PLEASE_INPUT_').lang('_EXCLAMATION_');
                    $this->ajaxReturn($result);
                }
                $invite = D('Ucenter/Invite')->getByCode($aCode);
                if ($invite) {
                    if ($invite['end_time'] > time()) {
                        $map['id'] = $invite['invite_type'];
                        $map['roles'] = array('like', '%[' . $aRoleId . ']%');
                        $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
                        if ($invite_type) {
                            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
                            if ($roleUser) {
                                $result['info'] = lang('_INFO_INV_ROLE_POSSESS_').lang('_EXCLAMATION_');
                            } else {
                                $memberModel = D('Common/Member');
                                $memberModel->logout();
                                $this->initInviteUser($uid, $aCode, $aRoleId);
                                UCenterMember()->initRoleUser($aRoleId, $uid);
                                clean_query_user_cache($uid,array('avatar64','avatar128','avatar32','avatar256','avatar512','rank_link'));
                                $memberModel->login($uid, false, $aRoleId); //登陆
                                $result['status'] = 1;
                                $result['url'] = U('Ucenter/Member/register', array('code' => $aCode));
                            }
                        } else {
                            $result['info'] = lang('_INFO_INV_HIGH_LEVEL_NEEDED_').lang('_EXCLAMATION_');
                        }
                    } else {
                        $result['info'] = lang('_INFO_INV_CODE_EXPIRED_');
                    }
                } else {
                    $result['info'] = lang('_INFO_NOT_EXIST_');
                }
            } else {
                $result['info'] = lang('_ERROR_ILLEGAL_OPERATE_').lang('_EXCLAMATION_');
            }
            $this->ajaxReturn($result);
        } else {
            $this->assign('role_id', $aRoleId);
            $this->display();
        }
    }

    /* 登录页面 */
    public function login()
    {
        if (request()->isPost()) {
            $result = controller('ucenter/Login', 'widget')->doLogin();

            if ($result['status']) {
                $this->success($result['info'], Input('post.from', Url('index/index/index'), 'text'));
            } else {
                $this->error($result['info']);
            }
        } else { //显示登录页面
            return $this->fetch();
        }
    }


    /* 快捷登录登录页面 */
    public function quickLogin()
    {
        if (IS_POST) {
            $result = A('Ucenter/Login', 'Widget')->doLogin();
            $this->ajaxReturn($result);
        } else { //显示登录弹出框
            $this->display();
        }
    }

    /* 退出登录 */
    public function logout()
    {
        if (is_login()) {
            model('Member')->logout();
            $this->success(lang('_SUCCESS_LOGOUT_').lang('_EXCLAMATION_'), Url('index/Index/index'));
        } else {
            $this->redirect('member/login');
        }
    }

    /* 验证码，用于登录和注册 */
    public function verify()
    {
        captcha_src();
    }

    /* 用户密码找回首页 */
    public function mi()
    {
        if (IS_POST) {
            $account = $username= I('post.account','','text');
            $type = I('post.type','','text');
            $password = I('post.password','','text');
            $verify = I('post.verify',0,'intval');//验证码
            //传入数据判断
            if(empty($account) || empty($type) || empty($password) || empty($verify)){
                $this->error(lang('_EMPTY_CANNOT_'));
            }
            check_username($username, $email, $mobile, $aUnType);
            //检查验证码是否正确
                $ret = D('Verify')->checkVerify($account,$type,$verify,0);
                if(!$ret){//验证码错误
                    $this->error(lang('_ERROR_VERIFY_CODE_'));
                }
                $resend_time =  modC('SMS_RESEND','60','USERCONFIG');
                if(time() > session('verify_time')+$resend_time ){//验证超时
                    $this->error(lang('_ERROR_VERIFY_OUTIME_'));
                }
                //获取用户UID
                switch ($type) {
                    case 'mobile':
                    $uid = UCenterMember()->where(array('mobile' => $account))->getField('id');
                    break;
                    case 'email':
                    $uid = UCenterMember()->where(array('email' => $account))->getField('id');
                    break;
                }
                if (!$uid) {
                    $this->error(lang('_ERROR_USED_1_') . lang('_USER_') . lang('_ERROR_USED_3_'));
                }
                //设置新密码
                $password = think_ucenter_md5($password, UC_AUTH_KEY);
                $data['id'] = $uid;
                $data['password'] = $password;
                //dump($data);exit;
                $ret = UCenterMember()->save($data);
                if($ret){
                    //返回成功信息前处理
                    clean_query_user_cache($uid, 'password');//删除缓存
                    D('user_token')->where('uid=' . $uid)->delete();
                    //返回数据
                    $this->success(lang('_SUCCESS_SETTINGS_'), U('Member/login'));
                }else{
                    $this->error();
                }
        } else {
            if (is_login()) {
                redirect(U('Home/Index/index'));
            }

            $this->display();
        }
    }

    /**
     * 重置密码
     */
    public function reset($uid, $verify)
    {
        //检查参数
        $uid = intval($uid);
        $verify = strval($verify);
        if (!$uid || !$verify) {
            $this->error(lang('_ERROR_PARAM_'));
        }

        //确认邮箱验证码正确
        $expectVerify = $this->getResetPasswordVerifyCode($uid);
        if ($expectVerify != $verify) {
            $this->error(lang('_ERROR_PARAM_'));
        }

        //将邮箱验证码储存在SESSION
        session('reset_password_uid', $uid);
        session('reset_password_verify', $verify);

        //显示新密码页面
        $this->display();
    }

    public function doReset($password, $repassword)
    {
        //确认两次输入的密码正确
        if ($password != $repassword) {
            $this->error(lang('_PW_NOT_SAME_'));
        }

        //读取SESSION中的验证信息
        $uid = session('reset_password_uid');
        $verify = session('reset_password_verify');

        //确认验证信息正确
        $expectVerify = $this->getResetPasswordVerifyCode($uid);
        if ($expectVerify != $verify) {
            $this->error(lang('_ERROR_VERIFY_INFO_INVALID_'));
        }

        //将新的密码写入数据库
        $data = array('id' => $uid, 'password' => $password);
        $model = UCenterMember();
        $data = $model->create($data);
        if (!$data) {
            $this->error(lang('_ERROR_PASSWORD_FORMAT_'));
        }
        $result = $model->where(array('id' => $uid))->save($data);
        if ($result === false) {
            $this->error(lang('_ERROR_DB_WRITE_'));
        }

        //显示成功消息
        $this->success(lang('_ERROR_PASSWORD_RESET_'), Url('Ucenter/Member/login'));
    }

    private function getResetPasswordVerifyCode($uid)
    {
        $user = UCenterMember()->where(array('id' => $uid))->find();
        $clear = implode('|', array($user['uid'], $user['username'], $user['last_login_time'], $user['password']));
        $verify = muucmf_hash($clear, UC_AUTH_KEY);
        return $verify;
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    public function showRegError($code = 0)
    {
        switch ($code) {
            case -1:
                $error = lang('').modC('USERNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('USERNAME_MAX_LENGTH',32,'USERCONFIG').lang('_ERROR_LENGTH_2_').lang('_EXCLAMATION_');
                break;
            case -2:
                $error = lang('_ERROR_USERNAME_FORBIDDEN_').lang('_EXCLAMATION_');
                break;
            case -3:
                $error = lang('_ERROR_USERNAME_USED_').lang('_EXCLAMATION_');
                break;
            case -4:
                $error = lang('_ERROR_LENGTH_PASSWORD_').lang('_EXCLAMATION_');
                break;
            case -5:
                $error = lang('_ERROR_EMAIL_FORMAT_2_').lang('_EXCLAMATION_');
                break;
            case -6:
                $error = lang('_ERROR_EMAIL_LENGTH_').lang('_EXCLAMATION_');
                break;
            case -7:
                $error = lang('_ERROR_EMAIL_FORBIDDEN_').lang('_EXCLAMATION_');
                break;
            case -8:
                $error = lang('_ERROR_EMAIL_USED_2_').lang('_EXCLAMATION_');
                break;
            case -9:
                $error = lang('_ERROR_PHONE_FORMAT_2_').lang('_EXCLAMATION_');
                break;
            case -10:
                $error = lang('_ERROR_FORBIDDEN_').lang('_EXCLAMATION_');
                break;
            case -11:
                $error = lang('_ERROR_PHONE_USED_').lang('_EXCLAMATION_');
                break;
            case -20:
                $error = lang('_ERROR_USERNAME_FORM_').lang('_EXCLAMATION_');
                break;
            case -30:
                $error = lang('_ERROR_NICKNAME_USED_').lang('_EXCLAMATION_');
                break;
            case -31:
                $error = lang('_ERROR_NICKNAME_FORBIDDEN_2_').lang('_EXCLAMATION_');
                break;
            case -32:
                $error =lang('_ERROR_NICKNAME_FORM_').lang('_EXCLAMATION_');
                break;
            case -33:
                $error = lang('_ERROR_LENGTH_NICKNAME_1_').modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG').lang('_ERROR_LENGTH_2_').lang('_EXCLAMATION_');;
                break;
            default:
                $error = lang('_ERROR_UNKNOWN_');
        }
        return $error;
    }


    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function profile()
    {
        if (!is_login()) {
            $this->error(lang('_ERROR_NOT_LOGIN_'), Url('User/login'));
        }
        if (IS_POST) {
            //获取参数
            $uid = is_login();
            $password = I('post.old');
            $repassword = I('post.repassword');
            $data['password'] = I('post.password');
            empty($password) && $this->error(lang('_ERROR_INPUT_ORIGIN_PASSWORD_'));
            empty($data['password']) && $this->error(lang('_ERROR_INPUT_NEW_PASSWORD_'));
            empty($repassword) && $this->error(lang('_ERROR_CONFIRM_PASSWORD_'));

            if ($data['password'] !== $repassword) {
                $this->error(lang('_ERROR_NOT_SAME_PASSWORD_'));
            }

            //$Api = new UserApi();
            //$res = $Api->updateInfo($uid, $password, $data);
            if ($res['status']) {
                $this->success(lang('_SUCCESS_CHANGE_PASSWORD_').lang('_EXCLAMAITON_'));
            } else {
                $this->error($res['info']);
            }
        } else {
            $this->display();
        }
    }

    /**
     * doSendVerify  发送验证码
     * @param $account
     * @param $verify
     * @param $type
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function doSendVerify($account, $verify, $type)
    {
        switch ($type) {
            case 'mobile':
                $content = modC('SMS_CONTENT', '{$verify}', 'USERCONFIG');
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = sendSMS($account, $content);
                return $res;
                break;
            case 'email':
                //发送验证邮箱
                $content = modC('REG_EMAIL_VERIFY', '{$verify}', 'USERCONFIG');
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = send_mail($account, modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config') . lang('_EMAIL_VERIFY_2_'), $content);
                return $res;
                break;
        }
    }

    /**
     * activate  提示激活页面
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function activate()
    {

        // $aUid = I('get.uid',0,'intval');
        $aUid = session('temp_login_uid');
        $status = UCenterMember()->where(array('id' => $aUid))->getField('status');
        if ($status != 3) {
            redirect(Url('ucenter/member/login'));
        }
        $info = query_user(array('uid', 'nickname', 'email'), $aUid);
        $this->assign($info);
        $this->display();
    }

    /**
     * reSend  重发邮件
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function reSend()
    {
        $res = $this->activateVerify();
        if ($res === true) {
            $this->success(lang('_SUCCESS_SEND_'), 'refresh');
        } else {
            $this->error(lang('_ERROR_SEND_') . $res, 'refresh');
        }
    }

    /**
     * changeEmail  更改邮箱
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function changeEmail()
    {
        $aEmail = I('post.email', '', 'op_t');
        $aUid = session('temp_login_uid');
        $ucenterMemberModel = UCenterMember();
        $ucenterMemberModel->where(array('id' => $aUid))->getField('status');
        if ($ucenterMemberModel->where(array('id' => $aUid))->getField('status') != 3) {
            $this->error(lang('_ERROR_AUTHORITY_LACK_').lang('_EXCLAMATION_'));
        }
        $ucenterMemberModel->where(array('id' => $aUid))->setField('email', $aEmail);
        clean_query_user_cache($aUid, 'email');
        $res = $this->activateVerify();
        $this->success(lang('_SUCCESS_CHANGE_'), 'refresh');
    }

    /**
     * activateVerify 添加激活验证
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    private function activateVerify()
    {
        $aUid = session('temp_login_uid');
        $email = UCenterMember()->where(array('id' => $aUid))->getField('email');
        $verify = D('Verify')->addVerify($email, 'email', $aUid);
        $res = $this->sendActivateEmail($email, $verify, $aUid); //发送激活邮件
        return $res;
    }

    /**
     * sendActivateEmail   发送激活邮件
     * @param $account
     * @param $verify
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    private function sendActivateEmail($account, $verify, $uid)
    {

        $url = 'http://' . $_SERVER['HTTP_HOST'] . U('ucenter/member/doActivate?account=' . $account . '&verify=' . $verify . '&type=email&uid=' . $uid);
        $content = modC('REG_EMAIL_ACTIVATE', '{$url}', 'USERCONFIG');
        $content = str_replace('{$url}', $url, $content);
        $content = str_replace('{$title}', modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config'), $content);
        $res = send_mail($account, modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config') . lang('_VERIFY_LETTER_'), $content);
        return $res;
    }

    /**
     * saveAvatar  保存头像
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function saveAvatar()
    {
        $redirect_url = session('temp_login_uid') ? U('Ucenter/member/step', array('step' => get_next_step('change_avatar'))) : 'refresh';
        $aCrop = I('post.crop', '', 'op_t');
        $aUid = session('temp_login_uid') ? session('temp_login_uid') : is_login();
        $aPath = I('post.path', '', 'op_t');
		
        if (empty($aCrop)) {
            $this->success(lang('_SUCCESS_SAVE_').lang('_EXCLAMATION_'),$redirect_url );
        }
        $returnPath = A('Ucenter/UploadAvatar', 'Widget')->cropPicture($aCrop,$aPath);
        $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');
        $data = array('uid' => $aUid, 'status' => 1, 'is_temp' => 0, 'path' => $returnPath,'driver'=> $driver, 'create_time' => time());
        $res = M('avatar')->where(array('uid' => $aUid))->save($data);
        if (!$res) {
            M('avatar')->add($data);
        }
        clean_query_user_cache($aUid, array('avatars','avatars_html'));
        $this->success(lang('_SUCCESS_AVATAR_CHANGE_').lang('_EXCLAMATION_'), $redirect_url);
    }

    /**
     * doActivate  激活步骤
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function doActivate()
    {
        $aAccount = I('get.account', '', 'op_t');
        $aVerify = I('get.verify', '', 'op_t');
        $aType = I('get.type', '', 'op_t');
        $aUid = I('get.uid', 0, 'intval');
        $check = D('Verify')->checkVerify($aAccount, $aType, $aVerify, $aUid);
        if ($check) {
            set_user_status($aUid, 1);
            $this->success(lang('_SUCCESS_ACTIVE_'), U('Ucenter/member/step', array('step' => get_next_step('start'))));
        } else {
            $this->error(lang('_FAIL_ACTIVE_').lang('_EXCLAMATION_'));
        }
    }

    /**
     * checkAccount  ajax验证用户帐号是否符合要求
     */
    public function checkAccount()
    {
        $aAccount = input('post.account', '', 'text');
        $aType = input('post.type', '', 'text');
        if (empty($aAccount)) {
            $this->error(lang('_EMPTY_CANNOT_').lang('_EXCLAMATION_'));
        }
        check_username($aAccount, $email, $mobile, $aUnType);
        $mUcenter = new UCenterMember;
        switch ($aType) {
            case 'username':
                empty($aAccount) && $this->error(lang('_ERROR_USERNAME_FORMAT_').lang('_EXCLAMATION_'));
                $length = mb_strlen($aAccount, 'utf-8'); // 当前数据长度
                if ($length < modC('USERNAME_MIN_LENGTH',2,'USERCONFIG') || $length > modC('USERNAME_MAX_LENGTH',32,'USERCONFIG')) {
                    $this->error(lang('_ERROR_USERNAME_LENGTH_1_').modC('USERNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('USERNAME_MAX_LENGTH',32,'USERCONFIG').lang('_ERROR_USERNAME_LENGTH_2_'));
                }


                $id = $mUcenter->where(array('username' => $aAccount))->value('id');
                if ($id) {
                    $this->error(lang('_ERROR_USERNAME_EXIST_2_'));
                }
                preg_match("/^[a-zA-Z0-9_]{".modC('USERNAME_MIN_LENGTH',2,'USERCONFIG').",".modC('USERNAME_MAX_LENGTH',32,'USERCONFIG')."}$/", $aAccount, $result);
                if (!$result) {
                    $this->error(lang('_ERROR_USERNAME_ONLY_PERMISSION_'));
                }
                break;
            case 'email':
                empty($email) && $this->error(lang('_ERROR_EMAIL_FORMAT_').lang('_EXCLAMATION_'));
                $length = mb_strlen($email, 'utf-8'); // 当前数据长度
                if ($length < 4 || $length > 32) {
                    $this->error(lang('_ERROR_EMAIL_EXIST_'));
                }

                $id = $mUcenter->where(array('email' => $email))->getField('id');
                if ($id) {

                    $this->error(lang('_ERROR_EMAIL_EXIST_'));
                }
                break;
            case 'mobile':
                empty($mobile) && $this->error(lang('_ERROR_PHONE_FORMAT_'));
                $id = $mUcenter->where(array('mobile' => $mobile))->getField('id');
                if ($id) {
                    $this->error(lang('_ERROR_PHONE_EXIST_'));
                }
                break;
        }
        $this->success(lang('_SUCCESS_VERIFY_'));
    }

    /**
     * checkNickname  ajax验证昵称是否符合要求
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function checkNickname()
    {
        $aNickname = input('post.nickname', '', 'text');

        if (empty($aNickname)) {
            $this->error(lang('_EMPTY_CANNOT_').lang('_EXCLAMATION_'));
        }

        $length = mb_strlen($aNickname, 'utf-8'); // 当前数据长度
        if ($length < modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG') || $length > modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG')) {
            $this->error(lang('_ERROR_NICKNAME_LENGTH_11_').modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG').lang('_ERROR_USERNAME_LENGTH_2_'));
        }

        $memberModel = model('member');
        $uid = $memberModel->where(array('nickname' => $aNickname))->value('uid');
        if ($uid) {
            $this->error(lang('_ERROR_NICKNAME_EXIST_'));
        }
        preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $aNickname, $result);
        if (!$result) {
            $this->error(lang('_ERROR_NICKNAME_ONLY_PERMISSION_'));
        }

        $this->success(lang('_SUCCESS_VERIFY_'));
    }

    /**
     * 切换登录身份
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function changeLoginRole()
    {
        $aRoleId = I('post.role_id', 0, 'intval');
        $uid = is_login();
        $data['status'] = 0;
        if ($uid && $aRoleId != get_login_role()) {
            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
            if ($roleUser) {
                $memberModel = D('Common/Member');
                $memberModel->logout();
                clean_query_user_cache($uid, array('avatar64', 'avatar128', 'avatar32', 'avatar256', 'avatar512', 'rank_link'));
                $result = $memberModel->login($uid, false, $aRoleId);
                if ($result) {
                    $data['info'] = lang('_INFO_ROLE_CHANGE_');
                    $data['status'] = 1;
                }
            }
        }
        $data['info'] = lang('_ERROR_ILLEGAL_OPERATE_');
        $this->ajaxReturn($data);
    }

    /**
     * 持有新身份
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function registerRole()
    {
        $aRoleId = I('post.role_id', 0, 'intval');
        $uid = is_login();
        $data['status'] = 0;
        if ($uid > 0 && $aRoleId != get_login_role()) {
            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
            if ($roleUser) {
                $data['info'] = lang('_INFO_INV_ROLE_POSSESS_');
                $this->ajaxReturn($data);
            } else {
                $memberModel = D('Common/Member');
                $memberModel->logout();
                UCenterMember()->initRoleUser($aRoleId, $uid);
                clean_query_user_cache($uid, array('avatar64', 'avatar128', 'avatar32', 'avatar256', 'avatar512', 'rank_link'));
                $memberModel->login($uid, false, $aRoleId); //登陆
            }
        } else {
            $data['info'] = lang('_ERROR_ILLEGAL_OPERATE_');
            $this->ajaxReturn($data);
        }
    }


    /**修改用户扩展信息
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function edit_expandinfo()
    {
        $result = A('Ucenter/RegStep', 'Widget')->edit_expandinfo();
        if ($result['status']) {
            $this->success(lang('_SUCCESS_SAVE_'), session('temp_login_uid') ? U('Ucenter/member/step', array('step' => get_next_step('expand_info'))) : 'refresh');
        } else {
            !isset($result['info']) && $result['info'] = lang('_ERROR_INFO_SAVE_NONE_');
            $this->error($result['info']);
        }
    }

    /**
     * 设置用户标签
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function set_tag()
    {
        $result = A('Ucenter/RegStep', 'Widget')->do_set_tag();
        if ($result['status']) {
            $result['url'] = U('Ucenter/member/step', array('step' => get_next_step('set_tag')));
        } else {
            !isset($result['info']) && $result['info'] = lang('_ERROR_INFO_SAVE_NONE_');
        }
        $this->ajaxReturn($result);
    }

    /**
     * 判断注册类型
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function checkRegisterType()
    {
        $aCode = input('get.code', '', 'text');
        $register_type = modC('REGISTER_TYPE', 'normal', 'Invite');
        $register_type = explode(',', $register_type);

        if (!in_array('invite', $register_type) && !in_array('normal', $register_type)) {
            $this->error(lang('_ERROR_WEBSITE_REG_CLOSED_'));
        }

        if (in_array('invite', $register_type) && $aCode != '') { //邀请注册开启且有邀请码
            $invite = model('Ucenter/Invite')->getByCode($aCode);
            if ($invite) {
                if ($invite['end_time'] <= time()) {
                    $this->error(lang('_ERROR_EXPIRED_').lang('_EXCLAMATION_'));
                } else { //获取注册角色
                    $map['id'] = $invite['invite_type'];
                    $invite_type = model('Ucenter/InviteType')->getSimpleData($map);
                    if ($invite_type) {
                        if (count($invite_type['roles'])) {
                            //角色
                            $map_role['status'] = 1;
                            $map_role['id'] = array('in', $invite_type['roles']);
                            $roleList = model('Admin/Role')->selectByMap($map_role, 'sort asc', 'id,title');
                            if (!count($roleList)) {
                                $this->error(lang('_ERROR_ROLE_').lang('_EXCLAMATION_'));
                            }
                            //角色end
                        } else {
                            //角色
                            $map_role['status'] = 1;
                            $map_role['invite'] = 0;
                            $roleList = model('Admin/Role')->selectByMap($map_role, 'sort asc', 'id,title');
                            //角色end
                        }
                        $this->assign('code', $aCode);
                        $this->assign('invite_user', $invite['user']);
                    } else {
                        $this->error(lang('_ERROR_FORBIDDEN_2_').lang('_EXCLAMATION_'));
                    }
                }
            } else {
                $this->error(lang('_ERROR_NOT_EXIST_').lang('_EXCLAMATION_'));
            }
        } else {
            //（开启邀请注册且无邀请码）或（只开启了普通注册）
            if (in_array('invite', $register_type)) {
                $this->assign('open_invite_register', 1);
            }else{
                $this->assign('open_invite_register', 0);
            }

            if (in_array('normal', $register_type)) {
                //角色
                $map_role['status'] = 1;
                $map_role['invite'] = 0;
                $roleList = model('Admin/Role')->selectByMap($map_role, 'sort asc', 'id,title');
                //角色end
            } else {
                //（只开启了邀请注册）
                $this->error(lang('_ERROR_NOT_INVITED_').lang('_EXCLAMATION_'));
            }
        }
        $this->assign('role_list', $roleList);
        return true;
    }

    /**
     * 判断邀请码是否可用
     * @param string $code
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function checkInviteCode($code = '')
    {
        if ($code == '') {
            return true;
        }
        $invite = D('Ucenter/Invite')->getByCode($code);
        if ($invite['end_time'] >= time()) {
            $map['id'] = $invite['invite_type'];
            $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
            if ($invite_type) {
                return true;
            }
        }
        return false;
    }

    private function initInviteUser($uid = 0, $code = '', $role = 0)
    {
        if ($code != '') {
            $inviteModel = model('ucenter/Invite');
            $invite = $inviteModel->getByCode($code);
            $data['inviter_id'] = abs($invite['uid']);
            $data['uid'] = $uid;
            $data['invite_id'] = $invite['id'];
            $result = D('Ucenter/InviteLog')->addData($data, $role);
            if ($result) {
                D('Ucenter/InviteUserInfo')->addSuccessNum($invite['invite_type'], abs($invite['uid']));

                $invite_info['already_num'] = $invite['already_num'] + 1;
                if ($invite_info['already_num'] == $invite['can_num']) {
                    $invite_info['status'] = 0;
                }
                $inviteModel->where(array('id' => $invite['id']))->save($invite_info);

                $map['id'] = $invite['invite_type'];
                $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
                if ($invite_type['is_follow']) {
                    $followModel = D('Common/Follow');
                    $followModel->addFollow($uid, abs($invite['uid']),1);
                    $followModel->addFollow(abs($invite['uid']), $uid,1);
                }
                if ($invite['uid'] > 0) {
                    D('Ucenter/Score')->setUserScore(array($invite['uid']), $invite_type['income_score'], $invite_type['income_score_type'], 'inc', '', 0, lang('_ERROR_BONUS_'));
                }
            }
        }
        return true;
    }

}