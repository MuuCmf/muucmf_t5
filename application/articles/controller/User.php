<?php
namespace app\articles\controller;

use app\common\controller\Common;
use think\Db;

/*用户类*/
class User extends Common
{

	public function edit()
	{
		$this->need_login();
	}

	public function my()
	{
		$this->need_login();
	}

	private function need_login()
	{
		if(!_need_login()){
			$this->error('需要登录');
		}
	}

}