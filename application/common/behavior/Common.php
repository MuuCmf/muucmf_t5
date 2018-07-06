<?php
namespace app\common\behavior;

use think\Config;
use think\Db;
use think\Lang;

class Common
{

    public function moduleInit(&$request)
    {
        //动态添加系统配置,非模块配置
        $config = cache('DB_CONFIG_DATA');
        if (!$config) {
            $map['status'] = 1;
            $map['group']=['>',0];
            $data = Db::name('Config')->where($map)->field('type,name,value')->select();
            
            foreach ($data as $value) {
                $config[$value['name']] = self::parse($value['type'], $value['value']);
            }
            cache('DB_CONFIG_DATA', $config);
        }
        Config::set($config); //动态添加配置
        // 判断站点是否关闭
        if (strtolower(request()->module()) != 'install' && strtolower(request()->module()) != 'admin') {
            if (!Config::get('WEB_SITE_CLOSE')) {
                header("Content-Type: text/html; charset=utf-8");
                echo Config::get('WEB_SITE_CLOSE_HINT');exit;
            }
        }
        // app_trace 调试模式后台设置
        if (Config::get('show_page_trace'))
        {
            Config::set('app_trace', true);
        }
        // 如果是开发模式那么将异常模板修改成官方的
        if (Config::get('app_debug'))
        {
            Config::set('exception_tmpl', THINK_PATH . 'tpl' . DS . 'think_exception.tpl');
        }
        // 如果是trace模式且Ajax的情况下关闭trace
        if (Config::get('app_trace') && $request->isAjax())
        {
            Config::set('app_trace', false);
        }
        

        // 加载插件语言包
        Lang::load([
            APP_PATH . 'common' . DS . 'lang' . DS . $request->langset() . DS . 'addon' . EXT,
        ]);
        // 切换多语言
        if (Config::get('lang_switch_on') && $request->get('lang'))
        {
            \think\Cookie::set('think_var', $request->get('lang'));
        }
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     */
    private static function parse($type, $value){
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if(strpos($value,':')){
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                }else{
                    $value =    $array;
                }
                break;
        }
        return $value;
    }   

}
