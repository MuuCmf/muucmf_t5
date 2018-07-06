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
        $default_multiInput = [
            ['type'=>'text','style'=>'width:295px;margin-right:5px'],
            ['type'=>'select','opt'=>get_time_unit(),'style'=>'width:100px']
        ];

        $builder
            ->title('DEMO设置')
            ->data($data);

        $builder
        	->title('title标题')
        	->suggest('suggest副标题');

        //输入类
        $builder
            ->keyUid('DEMO_UID','UID','UID用户ID')
        	->keyText('DEMO_TEXT', 'Text', 'Text文本')
            ->keyDefault('DEMO_TEXT','TEXT演示')

            ->keyTextArea('DEMO_TEXTAREA','TextArea文本域')
            ->keyDefault('DEMO_TEXTAREA',$default_textarea)

            ->keyInteger('DEMO_INTEGER','Integer','Integer整数')

            ->keyMultiInput('DEMO_MULTI_INPUT|DEMO_MULTI_INPUT2','MultiInput','',$default_multiInput);

            
        //选择类
        $builder
            ->keyColor('DEMO_COLOR','Color','')
            ->keyIcon('DEMO_ICON','Icon','图标')

            ->keyRadio('DEMO_RADIO', 'Radio', 'Radio 选择框', $default_arr)
            ->keyDefault('DEMO_RADIO',0)

            ->keyCheckBox('DEMO_CHECKBOX','CheckBox','CheckBox 选择框',$default_arr)

            ->keySelect('DEMO_SELECT','Select','select 选择框',$default_arr)

            ->keyChosen('DEMO_CHOSEN','Chosen','',$default_arr)
            ->keyDefault('DEMO_CHOSEN',0)

            ->keySwitch('DEMO_SWITCH','Switch','Switch 开关')

            ->keySingleUserGroup('DEMO_SINGLE_USERGROUP','SingleUserGroup','SingleUserGroup 用户权限组选择')
            ->keyMultiUserGroup('DEMO_MULTI_USERGROUP','MultiUserGroup','MultiUserGroup 用户权限组多选')

            ->keyCity('DEMO_CITY','City','City 城市联动选择')
            ->keyTime('DEMO_TIME','Time','')
            ->keyCreateTime()
            ->keyUpdateTime()

            ->keyStatus('DEMO_STATUS','Status','status状态')
            ->keyEditor('DEMO_EDITOR','Editor','Editor编辑器','wangeditor')
            ->group('表单元素', [
                'DEMO_UID',
                'DEMO_TEXT',
                'DEMO_INTEGER',
                'DEMO_TEXTAREA',
                'DEMO_MULTI_INPUT|DEMO_MULTI_INPUT2',

                'DEMO_EDITOR',
                //'DEMO_COLOR',
            	'DEMO_ICON',
                'DEMO_RADIO',
                'DEMO_CHECKBOX',
                'DEMO_SELECT',
                'DEMO_CHOSEN',
                'DEMO_SWITCH',
                'DEMO_SINGLE_USERGROUP',
                'DEMO_MULTI_USERGROUP',
                'DEMO_CITY',
                'DEMO_TIME',
                'create_time',
                'update_time',
                'DEMO_STATUS'
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
            	'DEMO_MULTIFILE',
        	]);

        //只读
        $builder
            ->keyId('DEMO_ID','ID','ID 编号')
            ->keyDefault('DEMO_ID','5487595412')
            ->keyLabel('DEMO_LABEL','Label','label')
            ->keyHidden('DEMO_HIDDEN','Hidden','只读隐藏文本')
            ->keyReadOnly('DEMO_READONLY','ReadOnly','只读文本')
            ->keyDefault('DEMO_READONLY','ReadOnly')

            ->keyReadOnlyText('DEMO_READONLYTEXT','ReadOnlyText','只读文本框')
            ->keyDefault('DEMO_READONLYTEXT','只读文本框')

            ->keyReadOnlyHtml('DEMO_READONLYHTML','ReadOnlyHtml','')
            ->keyDefault('DEMO_READONLYHTML','<h2>只读HTML<small>小标题</small></h2>')

            ->group('只读', [
                'DEMO_ID',

                'DEMO_LABEL',
                'DEMO_HIDDEN',
                'DEMO_READONLY',
                'DEMO_READONLYTEXT',
                'DEMO_READONLYHTML',
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
    public function listDemo()
    {   
        $optCategory = [
            [
                'id'=>1,
                'value'=>'分类1'
            ],
            [
                'id'=>2,
                'value'=>'分类2'
            ],
            [
                'id'=>3,
                'value'=>'分类3'
            ],
        ];
        $list = [
            [
                'id'=>1,
                'title'=>'title',
                'category'=>1,
                'description'=>'description',
                'sort'=>1,
                'status'=>1,
                'create_time'=>time(),
                'update_time'=>time(),
            ],
            [
                'id'=>1,
                'title'=>'title',
                'category'=>1,
                'description'=>'description',
                'sort'=>1,
                'status'=>0,
                'create_time'=>time(),
                'update_time'=>time(),
            ],
            [
                'id'=>1,
                'title'=>'title',
                'category'=>1,
                'description'=>'description',
                'sort'=>1,
                'status'=>-1,
                'create_time'=>time(),
                'update_time'=>time(),
            ],
        ];

    	$builder=new AdminListBuilder();
        $builder
            ->data($list)
            ->setSelectPostUrl(Url('DEMO/admin/index'))
            ->select('','cate','select','','','',$optCategory)
            
            ->buttonNew(Url('DEMO/editArticles'))
            ->buttonModalPopup(Url('DEMO/doAudit'),null,'审核不通过',array('data-title'=>'设置审核失败原因','target-form'=>'ids'))
            ->keyId()
            ->keyUid()
            ->keyText('title','标题')
            ->keyText('category','分类')
            ->keyText('description','摘要')
            ->keyText('sort','排序')
            ->keyStatus()
            ->keyCreateTime()
            ->keyUpdateTime();

        $builder->keyDoActionEdit('demo/editArticles?id=###');
        $builder->keyDoAction('demo/setDel?ids=###','回收站');
        //$builder->page($page);

    	$builder->display();
    }

    /**
     * 分类树
     * @return [type] [description]
     */
    public function tree()
    {
        $builder = new AdminTreeListBuilder();
        //demo 演示数据
        $tree = [
            [
                "id" => 1,
                "title" => "分类1",
                "sort" => 2,
                "pid" => 0,
                "status" => 1,
                '_'=>[
                    [
                        "id" => 55,
                        "title" => "子分类1",
                        "sort" => 1,
                        "pid" => 1,
                        "status" => 1,
                    ],[
                        "id" => 56,
                        "title" => "子分类2",
                        "sort" => 1,
                        "pid" => 1,
                        "status" => 1,
                    ]
                ]
            ],
            [
                "id" => 2,
                "title" => "分类2",
                "sort" => 1,
                "pid" => 0,
                "status" => 1,
            ],
            [
                "id" => 3,
                "title" => "分类3",
                "sort" => 1,
                "pid" => 0,
                "status" => 1,
            ]
        ];

        $builder
            ->title('分类树')
            ->suggest('分类树演示')
            ->buttonNew(Url('admin/add'))
            ->data($tree)
            ->display();
    }


}