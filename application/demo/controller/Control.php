<?php
namespace app\demo\controller;

use think\Db;
use think\Controller;
use app\common\controller\Common;

class Control extends Common
{
	/**
	 * 图标
	 * @return [type] [description]
	 */
	public function icon(){

        return $this->fetch();
    }
	/**
	 * 按钮
	 * @return [type] [description]
	 */
	public function button(){

        return $this->fetch();
    }
}