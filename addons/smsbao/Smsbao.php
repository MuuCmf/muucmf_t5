<?php
namespace addons\demo;

use app\common\controller\Addons;
use think\Db;

class SmsBao extends Addons
{
    /**
     * 插件信息
     * @var [type]
     */
    public $info = [
        'name' => 'SmsBao',
        'title' => '短信宝',
        'description' => '短信宝短信插件 http://www.smsbao.com/ ',
        'status' => 1,
        'author' => '@严大蒙同学',
        'version' => '1.1.0'
    ];
        
    /**
     * sms  短信钩子，必需，用于确定插件是短信服务
     * @return bool
     */
    public function sms()
    {
        return true;
    }

    /**
     * 发送短信
     * @param  [type] $mobile  手机号码
     * @param  [type] $content 发送内容
     * @return [type]          [description]
     */
    public function sendSms($mobile, $content){
        $uid = modC('SMS_UID', 'demo', 'CONFIG');
        $pwd = modC('SMS_PWD', '', 'CONFIG');

        if (empty($uid) || empty($pwd)) {
            return '管理员还未配置短信信息，请联系管理员配置'. $uid .$pwd;
        }

        $http = 'http://api.smsbao.com/sms';

        $url = $http .'?u=' . $uid . '&p=' . strtolower(md5($pwd)) . '&m=' . $mobile . '&c=' . urlencode($content);
        $return = file_get_contents($url);
        if ($return == 0) {
            return true;
        } else {
            return "发送失败! 状态：" . $return .' '. $this->getCode($return);
        }

    }


    private function getCode($code){
        switch($code){
            case 0: return '提交成功';
            case 30: return '密码错误';
            case 40: return '账号不存在';
            case 41: return '余额不足';
            case 42: return '帐号过期';
            case 43: return 'IP地址限制';
            case 50: return '内容含有敏感词';
            case 51: return '手机号码不正确';
            default : return '未知参数';
        }
    }

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }


}