<?php
namespace app\admin\controller;

use app\admin\controller\Admin;
use think\Db;
/**
 * 扩展后台插件管理页面
 */
class Addons extends Admin
{

    public function _initialize()
    {
        $this->assign('_extra_menu', array(
            lang('_ALREADY_INSTALLED_IN_THE_BACKGROUND_') => model('admin/Addons')->getAdminList(),
        ));
        parent::_initialize();
    }

    public function checkForm()
    {
        $data = $_POST;
        $data['info']['name'] = trim($data['info']['name']);
        if (!$data['info']['name'])
            $this->error(lang('_PLUGIN_LOGO_MUST_'));
        //检测插件名是否合法
        $addons_dir = ADDONS_PATH;
        if (file_exists("{$addons_dir}{$data['info']['name']}")) {
            $this->error(lang('_PLUGIN_ALREADY_EXISTS_'));
        }
        $this->success(lang('_CAN_CREATE_'));
    }



    /**
     * 插件列表
     */
    public function index()
    {
        $type = input('get.type', 'all', 'text');
        $list = model('admin/Addons')->getList('');
        $request = (array)input('request.');

        if ($type == 'yes') {//已安装的
            foreach ($list as $key => $value) {
                if ($value['uninstall'] != 1) {
                    unset($list[$key]);
                }
            }
        } else if ($type == 'no') {
            foreach ($list as $key => $value) {
                if ($value['uninstall'] == 1) {
                    $value['id'] = 0;
                    unset($list[$key]);
                }
            }
        } else {
            $type = 'all';
        }

        $this->setTitle(lang('_PLUGIN_LIST_'));
        $this->assign('type', $type);
        $this->assign('_list', $list);
        
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        return $this->fetch();
    }

    /**
     * 启用插件
     */
    public function enable()
    {
        $id = input('id');
        $msg = array('success' => lang('_ENABLE_SUCCESS_'), 'error' => lang('_ENABLE_FAILED_'));
        cache('hooks', null);

        $this->resume('Addons', "id={$id}", $msg);
    }

    /**
     * 禁用插件
     */
    public function disable()
    {
        $id = input('id');
        $msg = array('success' => lang('_DISABLE_SUCCESS_'), 'error' => lang('_DISABLE_'));
        cache('hooks', null);

        $this->forbid('Addons', "id={$id}", $msg);
    }

    /**
     * 设置插件页面
     */
    public function config()
    {
        $id = (int)input('id');
        $addon = Db::name('Addons')->find($id);
        if (!$addon)
            $this->error(lang('_PLUGIN_NOT_INSTALLED_'));

        $addon_class = get_addon_class($addon['name']);

        if (!class_exists($addon_class))
            trace(lang('_FAIL_ADDON_PARAM_',array('model'=>$addon['name'])), 'ADDONS', 'ERR');

        $data = new $addon_class;

        $addon['addon_path'] = $data->addons_path;
        $addon['custom_config'] = $data->custom_config;
        $this->meta_title = lang('_ADDONS_SET_') . $data->info['title'];
        $db_config = $addon['config'];
        $addon['config'] = include $data->config_file;
        if ($db_config) {
            $db_config = json_decode($db_config, true);
            foreach ($addon['config'] as $key => $value) {
                if ($value['type'] != 'group') {
                    $addon['config'][$key]['value'] = $db_config[$key];
                } else {
                    foreach ($value['options'] as $gourp => $options) {
                        foreach ($options['options'] as $gkey => $value) {
                            $addon['config'][$key]['options'][$gourp]['options'][$gkey]['value'] = $db_config[$gkey];
                        }
                    }
                }
            }
        }
        $this->assign('data', $addon);
        if ($addon['custom_config'])
            $this->assign('custom_config', $this->fetch($addon['addon_path'] . $addon['custom_config']));
        return $this->fetch();
    }

    /**
     * 保存插件设置
     */
    public function saveConfig()
    {
        $id = (int)input('id');
        $config = input('config/a');
        $flag = Db::name('Addons')->where(['id'=>$id])->setField('config', json_encode($config));
        if (isset($config['addons_cache'])) {//清除缓存
            cache($config['addons_cache'], null);
        }
        if ($flag !== false) {
            $this->success(lang('_SAVE_'), Cookie('__forward__'));
        } else {
            $this->error(lang('_SAVE_FAILED_'));
        }
    }

    /**
     * 安装插件
     */
    public function install()
    {
        $addon_name = trim(input('addon_name'));
        $addonsModel = model('admin/Addons');
        $rs = $addonsModel->install($addon_name);
        if ($rs === true) {
            $this->success(lang('_INSTALL_PLUG-IN_SUCCESS_'));
        } else {
            $this->error($addonsModel->getError());
        }
    }

    /**
     * 卸载插件
     */
    public function uninstall()
    {
        $addonsModel = Db::name('Addons');
        $id = trim(input('id'));
        $db_addons = Db::name('Addons')->find($id);

        $class = get_addon_class($db_addons['name']);

        $this->assign('jumpUrl', Url('index'));

        if (!$db_addons || !class_exists($class))
            $this->error(lang('_PLUGIN_DOES_NOT_EXIST_'));

        session('addons_uninstall_error', null);
        $addons = new $class;
        $uninstall_flag = $addons->uninstall();
        if (!$uninstall_flag)
            $this->error(lang('_EXECUTE_THE_PLUG-IN_TO_THE_PRE_UNLOAD_OPERATION_FAILED_') . session('addons_uninstall_error'));
        $hooks_update = model('Hooks')->removeHooks($db_addons['name']);
        if ($hooks_update === false) {
            $this->error(lang('_FAILED_HOOK_MOUNTED_DATA_UNINSTALL_PLUG-INS_'));
        }
        cache('hooks', null);
        $delete = Db::name('Addons')->where(['id'=>$id])->delete();
        if ($delete === false) {
            $this->error(lang('_UNINSTALL_PLUG-IN_FAILED_'));
        } else {
            $this->success(lang('_SUCCESS_UNINSTALL_'));
        }
    }

    /**
     * 钩子管理列表
     */
    public function hooks()
    {
        $this->setTitle(lang('_HOOK_LIST_'));
        $map = $fields = ['id'=>['>',0]];

        list($list,$page) = $this->lists('Hooks', $map, 'id desc', []);
        $list = $list->toArray()['data'];
        int_to_string($list, ['type' => config('HOOKS_TYPE')]);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        
        return $this->fetch();
    }

    public function addhook()
    {
        $this->assign('data', null);
        $this->setTitle(lang('_NEW_HOOK_'));
        return $this->fetch('edithook');
    }

    //钩子出编辑挂载插件页面
    public function edithook($id)
    {
        $hook = Db::name('Hooks')->field(true)->find($id);
        $this->assign('data', $hook);
        $this->setTitle(lang('_EDIT_HOOK_'));
        return $this->fetch('edithook');
    }

    //超级管理员删除钩子
    public function delhook($id)
    {
        if (Db::name('Hooks')->delete($id) !== false) {
            $this->success(lang('_DELETE_SUCCESS_'));
        } else {
            $this->error(lang('_DELETE_FAILED_'));
        }
    }

    /**
     * 编辑、新增钩子处理
     * @return [type] [description]
     */
    public function updateHook()
    {
        if(request()->isPost())
        {
            $data = input('');

            if ($data) {
                if ($data['id']) {
                    $flag = Db::name('Hooks')->where(['id'=>$data['id']])->update($data);
                    if ($flag !== false)
                        $this->success(lang('_UPDATE_'), Cookie('__forward__'));
                    else
                        $this->error(lang('_UPDATE_FAILED_'));
                } else {
                    $flag = Db::name('Hooks')->insert($data);
                    if ($flag)
                        $this->success(lang('_NEW_SUCCESS_'), Cookie('__forward__'));
                    else
                        $this->error(lang('_NEW_FAILURE_'));
                }
            } else {
                $this->error($hookModel->getError());
            }
        }
        
    }

    public function del($id = '', $name)
    {
        $ids = array_unique((array)input('ids', 0));

        if (empty($ids)) {
            $this->error(lang('_ERROR_DATA_SELECT_'));
        }

        $class = get_addon_class($name);
        if (!class_exists($class))
            $this->error(lang('_PLUGIN_DOES_NOT_EXIST_'));
        $addon = new $class();
        $param = $addon->admin_list;
        if (!$param)
            $this->error(lang('_THE_PLUGIN_LIST_INFORMATION_IS_NOT_CORRECT_'));
        extract($param);
        if (isset($model)) {
            $addonModel = model("Addons://{$name}/{$model}");
            if (!$addonModel)
                $this->error(lang('_MODEL_CANNOT_BE_REAL_'));
        }

        $map = array('id' => array('in', $ids));
        if ($addonModel->where($map)->delete()) {
            $this->success(lang('_DELETE_SUCCESS_'));
        } else {
            $this->error(lang('_DELETE_FAILED_'));
        }
    }

}
