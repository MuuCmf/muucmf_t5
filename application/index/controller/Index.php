<?php

namespace app\index\controller;

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

    public function demo() 
    {
        hook('demoTest',['id'=>100]);//自定义
    	return $this->fetch();
    }
}
