<?php
namespace app\demo\controller;

use think\Db;
use think\Controller;
use app\common\controller\Common;

class Index extends Common
{
	public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
    	return $this->fetch();
    }

    public function editor()
    {
    	return $this->fetch();
    }

    public function addon() 
    {
        //控制器执行插件
        //hook('demoTest',['id'=>100]);//自定义
        return $this->fetch();
    }

    public function action()
    {   
        //执行行为测试
        $res = action_log('demo', 'demo', 1, is_login());
        dump($res);
    }

}