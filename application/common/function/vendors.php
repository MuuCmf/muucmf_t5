<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 茉莉清茶 <57143976@qq.com> <http://www.3spp.cn>
// +----------------------------------------------------------------------


/**
 * 系统公共库文件扩展
 * 主要定义系统公共函数库扩展
 */

/**
 * 获取 IP  地理位置
 * 淘宝IP接口
 * @Return: array
 */
use think\Db;

function get_city_by_ip($ip)
{
    $url = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip;
    $ipinfo = json_decode(file_get_contents($url));
    if ($ipinfo->code == '1') {
        return false;
    }
    $city = $ipinfo->data->region . $ipinfo->data->city; //省市县
    $ip = $ipinfo->data->ip; //IP地址
    $ips = $ipinfo->data->isp; //运营商
    $guo = $ipinfo->data->country; //国家
    if ($guo == lang('_CHINA_')) {
        $guo = '';
    }
    return $guo . $city . $ips . '[' . $ip . ']';

}
/**
 * 发送验证码
 * @param $account
 * @param $verify
 * @param $type
 * @return bool|string
 */
function doSendVerify($account, $verify, $type)
{
    switch ($type) {
        case 'mobile':
            //发送手机短信验证
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
 * 系统邮件发送函数
 * @param string $to 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @茉莉清茶 57143976@qq.com
 */
function send_mail($to = '', $subject = '', $body = '', $name = '', $attachment = null)
{
    $host = config('MAIL_SMTP_HOST');
    $user = config('MAIL_SMTP_USER');
    $pass = config('MAIL_SMTP_PASS');
    if (empty($host) || empty($user) || empty($pass)) {
        return lang('_THE_ADMINISTRATOR_HAS_NOT_YET_CONFIGURED_THE_MESSAGE_INFORMATION_PLEASE_CONTACT_THE_ADMINISTRATOR_CONFIGURATION_');
    }

    if (is_sae()) {
        return sae_mail($to, $subject, $body, $name);
    } else {
        return send_mail_local($to, $subject, $body, $name, $attachment);
    }
}

/**
 * SAE邮件发送函数
 * @param string $to 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @茉莉清茶 57143976@qq.com
 */
function sae_mail($to = '', $subject = '', $body = '', $name = '')
{
    $site_name = modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config');
    if ($to == '') {
        $to = config('MAIL_SMTP_CE'); //邮件地址为空时，默认使用后台默认邮件测试地址
    }
    if ($name == '') {
        $name = $site_name; //发送者名称为空时，默认使用网站名称
    }
    if ($subject == '') {
        $subject = $site_name; //邮件主题为空时，默认使用网站标题
    }
    if ($body == '') {
        $body = $site_name; //邮件内容为空时，默认使用网站描述
    }
    $mail = new SaeMail();
    $mail->setOpt(array(
        'from' => config('MAIL_SMTP_USER'),
        'to' => $to,
        'smtp_host' => config('MAIL_SMTP_HOST'),
        'smtp_username' => config('MAIL_SMTP_USER'),
        'smtp_password' => config('MAIL_SMTP_PASS'),
        'subject' => $subject,
        'content' => $body,
        'content_type' => 'HTML'
    ));

    $ret = $mail->send();
    return $ret ? true : $mail->errmsg(); //返回错误信息
}

function is_sae()
{
    return function_exists('sae_debug');
}

function is_local()
{
    return strtolower(config('PICTURE_UPLOAD_DRIVER')) == 'local' ? true : false;
}

/**
 * 用常规方式发送邮件。
 */
function send_mail_local($to = '', $subject = '', $body = '', $name = '', $attachment = null)
{
    $from_email = config('MAIL_SMTP_USER');
    $from_name = modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config');

    if(config('MAIL_SMTP_SSL')){
        $ssl_value = 'ssl';
    }else{
        $ssl_value = '';
    }
    
    $mail = new \PHPMailer(); //实例化PHPMailer
    
    $mail->isSMTP(); // 设定使用SMTP服务
    $mail->SMTPDebug = 0; // 关闭SMTP调试功能// 1 = errors and messages// 2 = messages only
    $mail->SMTPAuth = true; // 启用 SMTP 验证功能
    $mail->SMTPSecure = $ssl_value; // 使用安全协议
    $mail->Host = config('MAIL_SMTP_HOST'); // SMTP 服务器
    $mail->Port = config('MAIL_SMTP_PORT'); // SMTP服务器的端口号
    $mail->Username = config('MAIL_SMTP_USER'); // SMTP服务器用户名
    $mail->Password = config('MAIL_SMTP_PASS'); // SMTP服务器密码
    $mail->CharSet = 'UTF-8';// 设置发送的邮件的编码
    $mail->From = $from_email;// 设置发件人邮箱地址 同登录账号
    $mail->FromName = $from_name;

    if ($to == '') {
        $to = config('MAIL_SMTP_CE'); //邮件地址为空时，默认使用后台默认邮件测试地址
    }
    if ($name == '') {
        $name = modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config'); //发送者名称为空时，默认使用网站名称
    }
    if ($subject == '') {
        $subject = modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config'); //邮件主题为空时，默认使用网站标题
    }
    if ($body == '') {
        $body = modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config'); //邮件内容为空时，默认使用网站描述
    }
    $mail->isHTML(true);// 邮件正文是否为html编码 注意此处是一个方法
    $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->Subject = $subject;
    $mail->MsgHTML($body);    //发送的邮件内容主体
    $mail->addAddress($to, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->addAttachment($file);
        }
    }

    $status = $mail->send(); //? true : $mail->ErrorInfo; //返回错误信息
    if($status) {
        return $status;
    }else{
        return $mail->ErrorInfo;
    }
    
}

function muucmf_hash($message, $salt = "MuuCmf")
{
    $s01 = $message . $salt;
    $s02 = md5($s01) . $salt;
    $s03 = sha1($s01) . md5($s02) . $salt;
    $s04 = $salt . md5($s03) . $salt . $s02;
    $s05 = $salt . sha1($s04) . md5($s04) . crc32($salt . $s04);
    return md5($s05);
}

/**获取模块的后台设置
 * @param        $key 获取模块的配置
 * @param string $default 默认值
 * @param string $module 模块名，不设置用当前模块名（admin模块采用控制器名）
 */
function modC($key, $default = '', $module = '')
{
    $module_name=request()->module();
    $mod = $module ? $module : $module_name;
    
    if($mod=="install"){
        return $default;
    }
    $result = cache('conf_' . strtoupper($mod) . '_' . strtoupper($key));
    if (empty($result)) {
        $config = Db::name('config')->where(['name' => '_' . strtoupper($mod) . '_' . strtoupper($key)])->find();

        if (!$config) {
            $result = $default;
        } else {
            if($config['value']==''){
                $result = $default;
            }else{
                $result = $config['value'];
            } 
        }
        cache('conf_' . strtoupper($mod) . '_' . strtoupper($key), $result, 3600);
    }
    return $result;
}

/**发送短消息
 * @param        $mobile 手机号码
 * @param        $content 内容
 * @return string
 * @auth 大蒙
 */
function sendSMS($mobile, $content)
{

    $sms_hook = modC('SMS_HOOK','none','CONFIG');
    $sms_hook =  check_sms_hook_is_exist($sms_hook);
    if($sms_hook == 'none'){
        return lang('_THE_ADMINISTRATOR_HAS_NOT_CONFIGURED_THE_SMS_SERVICE_PROVIDER_INFORMATION_PLEASE_CONTACT_THE_ADMINISTRATOR_');
    }
    //根据电信基础运营商的规定，每条短信必须附加短信签名，否则将无法正常发送。这里将后台设置的短信签名与内容拼接成发送内容
    $sms_sign = modC('SMS_SIGN','【MuuCmf】','CONFIG');
    $content = $sms_sign.$content;

    $name = get_addon_class($sms_hook);
    $class = new $name();
    return $class->sendSms($mobile,$content);

}


/**
 * get_kanban_config  获取看板配置
 * @param $key
 * @param $kanban
 * @param string $default
 * @param string $module
 * @return array|bool
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */
function get_kanban_config($key, $kanban, $default = '', $module = '')
{
    $config = modC($key, $default, $module);
    if (is_array($config)) {
        return $config;
    } else {
        $config = json_decode($config, true);
        foreach ($config as $v) {
            if ($v['id'] == $kanban) {
                $res = $v['items'];
                break;
            }
        }
        return getSubByKey($res, 'id');
    }
}

/**
 * @param $data 二维码包含的文字内容
 * @param $filename 保存二维码输出的文件名称，*.png
 * @param bool $picPath 二维码输出的路径
 * @param bool $logo 二维码中包含的LOGO图片路径
 * @param string $size 二维码的大小
 * @param string $level 二维码编码纠错级别：L、M、Q、H
 * @param int $padding 二维码边框的间距
 * @param bool $saveandprint 是否保存到文件并在浏览器直接输出，true:同时保存和输出，false:只保存文件
 * return string
 */
function qrcode($data,$filename,$picPath=false,$logo=false,$size='4',$level='L',$padding=2,$saveandprint=false){
    

    // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
    $path = $picPath?$picPath:PUBLIC_PATH. DS. "uploads". DS ."picture". DS ."QRcode"; //图片输出路径
    if(!is_dir($path)){
        mkdir($path);
    }

    //在二维码上面添加LOGO
    if(empty($logo) || $logo=== false) { //不包含LOGO
        if ($filename==false) {
            \PHPQRCode\QRcode::png($data, false, $level, $size, $padding, $saveandprint); //直接输出到浏览器，不含LOGO
        }else{
            $filename=$path.'/'.$filename; //合成路径
            \PHPQRCode\QRcode::png($data, $filename, $level, $size, $padding, $saveandprint); //直接输出到浏览器，不含LOGO
        }
    }else { //包含LOGO
        if ($filename==false){
            //$filename=tempnam('','').'.png';//生成临时文件
            die(lang('_PARAMETER_ERROR_'));
        }else {
            //生成二维码,保存到文件
            $filename = $path . '\\' . $filename; //合成路径
        }
        \PHPQRCode\QRcode::png($data, $filename, $level, $size, $padding);

        $QR = imagecreatefromstring(file_get_contents($filename));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        if ($filename === false) {
            Header("Content-type: image/png");
            imagepng($QR);
        } else {
            if ($saveandprint === true) {
                imagepng($QR, $filename);
                header("Content-type: image/png");//输出到浏览器
                imagepng($QR);
            } else {
                imagepng($QR, $filename);
            }
        }
    }
    return $filename;
}
