<?php

namespace app\index\controller;

use app\common\controller\Common;
use think\Lang;

class Index extends Common
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        return $this->view->fetch();
    }
}
