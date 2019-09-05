<?php
namespace app\admin\Controller;

use think\Db;
use app\admin\model\AuthRule;
use app\admin\model\AuthGroup;
use think\Controller;

class Debug extends Controller
{	
	public $is_root = 1;

	public function _initialize()
    {

    	//$menu = $this->getTreeMenus('A5B675E5-BF68-D25F-2F1E-6BEC1CD66587');
    }

    public function index()
    {
    	echo 'debug';
    	dump(controller('admin/Admin')->getTreeMenus('A5B675E5-BF68-D25F-2F1E-6BEC1CD66587'));
    }

}