<?php
namespace app\common\behavior;

use think\App;
use think\Hook;
use think\Db;
use think\Loader;
use think\Config;
use think\Cache;

// 初始化钩子信息
class InitHook {

    // 行为扩展的执行入口必须是run
    public function run(&$params){

        $data = cache('hooks');
        if(!$data){
            $hooks = collection(Db::name('Hooks')->field('addons')->select())->toArray();

            
            foreach ($hooks as $key => $value) {

                if(is_array($value)){
                    $map['status']  =   1;
                    $names          =   $value;
                    $map['name']    =   ['in',$names];
                    $data = Db::name('Addons')->where($map)->Field('id,name')->find();
                    if($data){

                        $addons = array_intersect($names, $data);
                        
                        Hook::add($key,array_map('get_addon_class',$addons));
                    }
                }
            }
            cache('hooks',Hook::get());
        }else{
            Hook::import($data,false);
        }


        // 注册类的根命名空间
        Loader::addNamespace('addons', ADDONS_PATH);

        //自动识别插件目录配置
        // 获取开关
        //$autoload = (bool)Config::get('addons.autoload', false);
        // 非正是返回
        //if (!$autoload) {
        //    return;
        //}
        // 当debug时不缓存配置
        $config = App::$debug ? [] : Cache::get('addons', []);


        if (empty($config)) {
            // 读取addons的配置
            $config = (array)Config::get('addons');
            // 读取插件目录及钩子列表
            $base = get_class_methods("\\think\\Addons");
            
            // 读取插件目录中的php文件
            foreach (glob(ADDONS_PATH . '*/*.php') as $addons_file) {
                // 格式化路径信息
                $info = pathinfo($addons_file);
                // 获取插件目录名
                $name = pathinfo($info['dirname'], PATHINFO_FILENAME);

                // 找到插件入口文件
                if (strtolower($info['filename']) == strtolower($name)) {
                    
                    // 读取出所有公共方法
                    //$methods = (array)get_class_methods("\\addons\\" . $name . "\\" . $info['filename']);

                    // 跟插件基类方法做比对，得到差异结果
                    //$hooks = array_diff($methods, $base);
                    
                }
            }
            
        }
    }
}