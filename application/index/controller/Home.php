<?php
namespace app\index\controller;

use app\admin\builder\AdminConfigBuilder;
use app\admin\controller\Admin;


class Home extends Admin
{
    public function _initialize()
    {
        parent::_initialize();
    }


    public function config()
    {
        $builder = new AdminConfigBuilder();
        $data = $builder->handleConfig();
        $builder->title(lang('_HOME_SETTING_'));

        $builder->keyRadio('HOME_INDEX_TYPE','系统首页类型','',array('static_home'=>'静态首页','index'=>'聚合首页','login'=>'登录页'));
        $modules = model('Common/Module')->getAll();
        foreach ($modules as $m) {
            if ($m['is_setup'] == 1 && $m['entry'] != '') {
                if (file_exists(APP_PATH . $m['name'] . '/widget/IndexBlock.php')) {
                    $module[] = array('data-id' => $m['name'], 'title' => $m['alias']);
                }
            }
        }
        $module[] = ['id' => 'slider', 'title' => lang('_CAROUSEL_')];

        $default = [
            array('id' => 'disable', 'title' => lang('_DISABLED_'), 'items' => $module), 
            array('id' => 'enable', 'title' =>lang('_ENABLED_'), 'items' => [])
        ];
        empty($data['BLOCK']) && $data['BLOCK'] = '';
        $data['BLOCK'] = $builder->parseKanbanArray($data['BLOCK'],$module,$default);

        $builder->keyKanban('BLOCK', lang('_DISPLAY_BLOCK_'),lang('_TIP_DISPLAY_BLOCK_'));
        $builder->group('首页类型', 'HOME_INDEX_TYPE');
        $builder->group('聚合首页展示模块', 'BLOCK');
        $show_blocks = get_kanban_config('BLOCK_SORT', 'enable', array(), 'Home');
        $builder->buttonSubmit();
        $builder->data($data);
        $builder->display();
    }

}