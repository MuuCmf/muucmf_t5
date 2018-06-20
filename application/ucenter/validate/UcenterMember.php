<?php
namespace app\ucenter\validate;

use think\Validate;

class UcenterMember extends Validate
{
    //需要验证的键值
    protected $rule =   [
        'email'              => "email|unique:ucenter_member",  //验证邮箱|验证邮箱在表中唯一性
        'username'           => "unique|length:6,30",
        'password'           => 'require|length:6,30',
        'confirm_password'   => 'require|length:6,30|confirm:password',
    ];

    //验证不符返回msg
    protected $message  =   [
        'email.email'               => '请输入正确邮箱地址！',
        'email.unique'              => '邮箱地址已存在',
        'username.unique'           => '用户名已存在',
        'username.length'           => '用户名应在6-30之间',
        'password.require'          => '密码不能为空',
        'password.length'           => '密码应在6-30之间',
        'confirm_password.require'  => '确认密码不能为空',
        'confirm_password.length'   => '确认密码应在6-30之间',
        'confirm_password.confirm'  => '确认密码与密码内容不一致',  
    ];
    //验证场景
    protected $scene = [
        'password'  =>  ['password','confirm_password'],
    ];
}