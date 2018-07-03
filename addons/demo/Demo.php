<?php
namespace addons\demo;

use app\common\controller\Addons;
use think\Db;


    class Demo extends Addons{

        public $info = [
            //插件目录
            'name'=>'demo',
            //插件名
            'title'=>'demo测试',
            //插件描述
            'description'=>'这就是个案例',
            //开发者
            'author'=>'muucmf',
            //版本号
            'version'=>'1.0.0'
        ];

        public function install(){

            
            return true;
        }

        public function uninstall(){
            
            return true;
        }

        public function demoTest($param){

            echo '这是一个测试';
            dump($param);

        }
    }