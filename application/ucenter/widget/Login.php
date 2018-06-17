<?php
namespace app\ucenter\widget;

use think\Controller;
use think\Db;

class Login extends Controller
{
    public function login($type = "quickLogin")
    {
        if ($type != "quickLogin") {
            if (is_login()) {
                redirect(Url('Index/Index/index'));
            }
        }
        $this->assign('login_type', $type);
        $ph = array();
        check_login_type('username') && $ph[] = lang('_USERNAME_');
        check_login_type('email') && $ph[] = lang('_EMAIL_');
        check_login_type('mobile') && $ph[] = lang('_PHONE_');
        $this->assign('ph', implode('/', $ph));
        return $this->fetch('ucenter@widget/login');
    }

    public function doLogin()
    {
        $aUsername = $username = input('param.username','','text');
        $aPassword = input('param.password', '', 'text');
        $aVerify = input('param.verify', '', 'text');
        $aRemember = input('param.remember', 0, 'intval');//默认记住登录 0：不记住；1：记住

        if(empty($aUsername)) $this->error(lang('_MI_USERNAME_'));
        /* 检测验证码 */
        if (check_verify_open('login')) {
            if (!check_verify($aVerify)) {
                $res['info']=lang('_INFO_VERIFY_CODE_INPUT_ERROR_').lang('_PERIOD_');
                return $res;
            }
        }

        /* 调用UC登录接口登录 */
        check_username($aUsername, $email, $mobile, $aUnType);
        //echo $aUnType;exit;
        if (!check_reg_type($aUnType)) {
            $res['info']=lang('_INFO_TYPE_NOT_OPENED_').lang('_PERIOD_');
        }

        $uid = model('UcenterMember')->login($username, $aPassword, $aUnType);
        if (0 < $uid) { //登录成功
            /* 登录用户 */
            $Member = model('Member');
            $args['uid'] = $uid;
            $args = array('uid'=>$uid,'nickname'=>$username);
            check_and_add($args);

            if ($Member->login($uid, $aRemember == 1)) { //登录用户
                //TODO:跳转到登录前页面
                //echo $uid;exit;
                $html_uc = '';
                //if (UC_SYNC && $uid != 1) {
                    //include_once './api/uc_client/client.php';
                    //同步登录到UC
                    $ref = Db::name('ucenter_user_link')->where(array('uid' => $uid))->find();
                    //$html_uc = uc_user_synlogin($ref['uc_uid']);
                //}
                $html = $html_uc;
                $res['status']=1;
                $res['info']=$html;
                $res['uid']=$uid;
                //$this->success($html, get_nav_url(C('AFTER_LOGIN_JUMP_URL')));
            } else {
                $res['status']=0;
                $res['info']=$Member->getError();
            }

        } else { //登录失败
            switch ($uid) {
                case -1:
                    $res['status']=0;
                    $res['info']= lang('_INFO_USER_FORBIDDEN_');
                    break; //系统级别禁用
                case -2:
                    $res['status']=0;
                    $res['info']= lang('_INFO_PW_ERROR_').lang('_EXCLAMATION_');
                    break;
                default:
                    $res['status']=0;
                    $res['info']= $uid;
                    break; // 0-接口参数错误（调试阶段使用）
            }
        }
        return $res;
    }
} 