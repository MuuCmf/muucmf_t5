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
        $data='';

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

        	->keyReadOnly('DEMO_READONLY','ReadOnly','只读文本')
        	->keyDefault('DEMO_READONLY','ReadOnly')

        	->keyReadOnlyText('DEMO_READONLYTEXT','ReadOnlyText','只读文本框')
        	->keyDefault('DEMO_READONLYTEXT','只读文本框')

        	->keyText('DEMO_TEXT', 'Text', 'Text副标题')
            ->keyDefault('DEMO_TEXT','TEXT演示')

            ->keyTextArea('DEMO_TEXTAREA','TextArea')
            ->keyDefault('DEMO_TEXTAREA',$default_textarea)

            ->keyReadOnlyHtml('DEMO_READONLYHTML','ReadOnlyHtml','')
            ->keyDefault('DEMO_READONLYHTML','<h2>只读HTML<small>小标题</small></h2>')

            ->keyColor('DEMO_COLOR','Color','')

            ->keyInteger('DEMO_INTEGER','Integer','')

            ->keyIcon('DEMO_ICON','Icon','图标')

            ->keyRadio('DEMO_RADIO', 'Radio', 'Radio副标题', $default_arr)
            ->keyDefault('DEMO_RADIO',0)

            ->keyCheckBox('DEMO_CHECKBOX','CheckBox','CheckBox副标题',$default_arr)

            ->keySelect('DEMO_SELECT','Select','select副标题',$default_arr)

            ->keyChosen('DEMO_CHOSEN','Chosen','',$default_arr)
            ->keyDefault('DEMO_CHOSEN',0)

            ->keyTime('DEMO_TIME','Time','')


            ->group('分组1', [
            	'DEMO_READONLY',
            	'DEMO_READONLYTEXT',
            	'DEMO_TEXT',
            	'DEMO_INTEGER',
            	'DEMO_TEXTAREA',
            	'DEMO_READONLYHTML',
            	//'DEMO_COLOR',
            	'DEMO_ICON',
            	'DEMO_RADIO',
            	'DEMO_CHECKBOX',
            	'DEMO_SELECT',
            	'DEMO_CHOSEN',
            	'DEMO_TIME',

        	])
            ->group('分组2', [
            	
        	]);

			//上传
			$builder
			->keySingleImage('DEMO_SINGLEIMAGE','SingleImage','单图片上传')

			->keyMultiImage('DEMO_MULTIIMAGE','MultImage','多图片上传')

            ->keySingleFile('DEMO_SINGFILE','SingFile','单文件上传')

            ->keyMultiFile('DEMO_MULTIFILE','MultiFile','多文件上传')

        	->group('上传', [
        		'DEMO_SINGLEIMAGE',
        		'DEMO_MULTIIMAGE',
            	'DEMO_SINGFILE',
            	//'DEMO_MULTIFILE',
        	]);

        	//提交
        	$builder
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