<?php
namespace app\common\behavior;

use think\Hook;
use think\Db;
use think\Loader;
use think\Cache;
use think\Route;

// 初始化钩子信息
class InitHook {

    // 行为扩展的执行入口必须是run
    public function run(&$params){

        // 注册类的根命名空间
        Loader::addNamespace('addons', ADDONS_PATH);
        // 定义路由
        Route::any('addons/execute/:route', "\\muucmf\\addons\\Route@execute");

        $data = Cache::get('hooks');
        if(!$data){
            $hooks = collection(Db::name('Hooks')->column('name,addons'))->toArray();

            foreach ($hooks as $key => $value) {
                
                $map['status']  =   1;
                $names          =   explode(',',$value);
                $map['name']    =   ['in',$names];
                $data = Db::name('Addons')->where($map)->column('id,name');
                if($data){
                    $addons = array_filter(array_map('get_addon_class', $data));
                    Hook::add($key,$addons);
                }
            }
            Cache::set('hooks',Hook::get());
        }else{
            Hook::import($data,false);
        }
    }
}