<?php
namespace app\demo\controller;

use think\Db;
use app\admin\controller\Admin as MuuAdmin;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminTreeListBuilder;
use app\common\model\ContentHandler;

class Admin extends MuuAdmin
{

	public function _initialize()
    {
        parent::_initialize();
    }

   	/**
   	 * 配置
   	 * @return [type] [description]
   	 */
    public function config(){

		$builder=new AdminConfigBuilder();
        $data=$builder->handleConfig();
        $default_textarea='文本域默认值';

        $default_arr=[
        	0=>'选项1',
        	1=>'选项2',
        	2=>'选项3'
        ];

        $builder
            ->title('DEMO设置')
            ->data($data);

        $builder
        	->title('title标题')
        	->suggest('suggest副标题')
            ->keyTextArea('DEMO_TEXTAREA','TextArea')
            ->keyDefault('DEMO_TEXTAREA',$default_textarea)

            ->keyText('DEMO_TEXT', 'Text', 'Text副标题')
            ->keyDefault('DEMO_TEXT','TEXT演示')

            ->keyRadio('DEMO_RADIO', 'Radio', 'Radio副标题', $default_arr)
            ->keyDefault('DEMO_RADIO',0)

            ->keyCheckBox('DEMO_CHECKBOX','CheckBox','CheckBox副标题',$default_arr)

            ->keySelect('DEMO_SELECT','Select','select副标题',$default_arr)

            ->keyChosen('DEMO_CHOSEN','Chosen','',$default_arr)
            ->keyDefault('DEMO_CHOSEN',0)

            ->group('分组1', [
            	'DEMO_TEXT',
            	'DEMO_TEXTAREA',
            	'DEMO_RADIO',
            	'DEMO_CHECKBOX',
            	'DEMO_SELECT',
            	'DEMO_CHOSEN',

        	])
            ->group('分组2', '')

            ->buttonSubmit()
            ->buttonBack()
            ->display();

    }

    /**
     * 列表
     * @return [type] [description]
     */
    public function list(){

    	$builder=new AdminListBuilder();

    	$builder->display();
    }

    /**
     * 分类树
     * @return [type] [description]
     */
    public function tree(){

    	
    }

}