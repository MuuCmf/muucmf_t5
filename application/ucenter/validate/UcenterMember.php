<?php
namespace app\ucenter\validate;

use think\Validate;

class UcenterMember extends Validate
{
    //需要验证的键值
    protected $rule =   [
        'email'              => "email|unique:ucenter_member",  //验证邮箱|验证邮箱在表中唯一性
        'username'           => "unique:ucenter_member",
        'username'           => 'checkUsername|checkUsernameLength',
        'mobile'             => "mobile|unique:ucenter_member",
        'password'           => 'require|length:6,30',
        'confirm_password'   => 'require|length:6,30|confirm:password',
    ];

    //验证不符返回msg
    protected $message  =   [
        'email.email'               => '请输入正确邮箱地址！',
        'email.unique'              => '邮箱地址已存在',
        'username.unique'           => -3,//'用户名已存在',
        'mobile.unique'             => '手机号码已经存在',
        'password.require'          => '密码不能为空',
        'password.length'           => '密码应在6-30之间',
        'confirm_password.require'  => '确认密码不能为空',
        'confirm_password.length'   => '确认密码应在6-30之间',
        'confirm_password.confirm'  => '确认密码与密码内容不一致',  
    ];
    //验证场景
    protected $scene = [
        'password'  =>  ['password','confirm_password'],
        'reg'  =>  ['username','email','password'],
    ];

    // 自定义验证规则
    protected function checkUsernameLength($value)
    {
        $length = mb_strlen($value, 'utf-8'); // 当前数据长度
        if ($length < modC('USERNAME_MIN_LENGTH',2,'USERCONFIG') || $length > modC('USERNAME_MAX_LENGTH',32,'USERCONFIG')) {
            return -1;
        }
        return true;
    }

    protected function checkUsername($value)
    {

        //如果用户名中有空格，不允许注册
        if (strpos($value, ' ') !== false) {
            return false;
        }
        preg_match("/^[a-zA-Z0-9_]{0,64}$/", $value, $result);

        if (!$result) {
            return -12;
        }
        return true;
    }
}