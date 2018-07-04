<?php
namespace addons\demo\controller;



class Admin extends \muucmf\addons\Admin
{


	public function index(){

		
		$this->setTitle('插件管理后台');
		return $this->fetch();
	}

}