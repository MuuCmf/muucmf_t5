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
        
        // 文章首页
        $this->assign('enter', get_nav_url($enter));
        return $this->fetch();
    }

    
}
