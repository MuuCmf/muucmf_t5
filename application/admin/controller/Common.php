<?php
namespace app\admin\Controller;

use think\Controller;

class Common extends Controller
{

    /**
     * 后台用户登录
     */
    public function login($username = null, $password = null, $verify = null)
    {
        if(request()->isPost()){
            /* 检测验证码 TODO: */
            if (APP_DEBUG==false){
                if(!check_verify($verify)){
                    $this->error(lang('_VERIFICATION_CODE_INPUT_ERROR_'));
                }
            }

            /* 调用UC登录接口登录 */
            $User = new UserApi;
            $uid = $User->login($username, $password);
            if(0 < $uid){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success(lang('_LOGIN_SUCCESS_'), U('Index/index'));
                } else {
                    $this->error($Member->getError());
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = lang('_USERS_DO_NOT_EXIST_OR_ARE_DISABLED_'); break; //系统级别禁用
                    case -2: $error = lang('_PASSWORD_ERROR_'); break;
                    default: $error = lang('_UNKNOWN_ERROR_'); break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            if(is_login()){
                $this->redirect('Index/index');
            }else{
                
                return $this->fetch();
            }
        }
    }

    /* 退出登录 */
    public function logout(){
        if(is_login()){
            D('Member')->logout();
            session('[destroy]');
            $this->success(lang('_EXIT_SUCCESS_'), U('login'));
        } else {
            $this->redirect('login');
        }
    }

    public function verify(){
        verify();
        // $verify = new \Think\Verify();
        // $verify->entry(1);
    }

}