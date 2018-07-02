<?php
namespace app\admin\model;

use think\Model;

/**
 * 插件模型
 */
class Addons extends Model
{
    /**
     * 查找后置操作
     */
    protected function _after_find(&$result, $options)
    {

    }

    protected function _after_select(&$result, $options)
    {

        foreach ($result as &$record) {
            $this->_after_find($record, $options);
        }
    }

    public function install($name)
    {

        $class = get_addon_class($name);
        if (!class_exists($class)) {
            $this->error = lang('_PLUGIN_DOES_NOT_EXIST_');
            return false;
        }
        $addons = new $class;
        $info = $addons->info;
        if (!$info || !$addons->checkInfo())//检测信息的正确性
        {
            $this->error = lang('_PLUGIN_INFORMATION_MISSING_');
            return false;
        }
        session('addons_install_error', null);
        $install_flag = $addons->install();
        if (!$install_flag) {
            $this->error = lang('_PERFORM_A_PLUG__IN__OPERATION_FAILED_') . session('addons_install_error');
            return false;
        }
        $addonsModel = model('Addons');
        $data = $addonsModel->create($info);

        if ((is_array($addons->admin_list) && $addons->admin_list !== array()) || method_exists(A('Addons://Mail/Admin'), 'buildList')) {
            $data['has_adminlist'] = 1;
        } else {
            $data['has_adminlist'] = 0;
        }
        if (!$data) {
            $this->error = $addonsModel->getError();
            return false;
        }
        if ($addonsModel->add($data)) {
            $config = array('config' => json_encode($addons->getConfig()));
            $addonsModel->where("name='{$name}'")->save($config);
            $hooks_update = D('Hooks')->updateHooks($name);
            if ($hooks_update) {
                S('hooks', null);
                return true;
            } else {
                $addonsModel->where("name='{$name}'")->delete();
                $this->error = lang('_THE_UPDATE_HOOK_IS_FAILED_PLEASE_TRY_TO_REINSTALL_');
                return false;
            }

        } else {
            $this->error = lang('_WRITE_PLUGIN_DATA_FAILED_');
            return false;
        }
    }


    /**
     * 获取插件列表
     * @param string $addon_dir
     */
    public function getList()
    {
        $addon_dir = ADDONS_PATH;
        
        if(is_dir($addon_dir)){
            $dirs = array_map('basename', glob($addon_dir . '*', GLOB_ONLYDIR));
        }
        
        if ($dirs === FALSE || !file_exists($addon_dir)) {
            $this->error = lang('_THE_PLUGIN_DIRECTORY_IS_NOT_READABLE_OR_NOT_');
            return FALSE;
        }

        $addons = [];
        $where['name'] = ['in', $dirs];
        $list = collection($this->where($where)->select())->toArray();

        //dump($list);exit;
        foreach ($list as $addon) {
            $addon['uninstall'] = 0;
            $file = $addon_dir.$addon['name'].'/icon.png';
            if(file_exists($file)){
                $addon['icon_photo'] = $addon_dir.$addon['name'].'/icon.png';
            }else{
                $addon['icon_photo'] = '';
            }
            $addons[$addon['name']] = $addon;
        }

        foreach ($dirs as $value) {

            if (!isset($addons[$value])) {
                $class = get_addon_class($value);
                if (!class_exists($class)) { // 实例化插件失败忽略执行
                    \Think\Log::record(lang('_PLUGIN_') . $value . lang('_THE_ENTRY_FILE_DOES_NOT_EXIST_WITH_EXCLAMATION_'));
                    continue;
                }
                $obj = new $class;
                $addons[$value] = $obj->info;
                if ($addons[$value]) {
                    $addons[$value]['uninstall'] = 1;
                    unset($addons[$value]['status']);
                }
                
                $file = $addon_dir.$value.'/icon.png';
                if(file_exists($file)){
                    $addons[$value]['icon_photo'] = $addon_dir.$value.'/icon.png';
                }else{
                    $addons[$value]['icon_photo'] = '';
                }
            }
        }
        
        int_to_string($addons, ['status' => [-1 => lang('_DAMAGE_'), 0 => lang('_DISABLE_'), 1 => lang('_ENABLE_'), null => lang('_NOT_INSTALLED_')]]);

        $addons = list_sort_by($addons, 'uninstall', 'desc');
        return $addons;
    }

    /**
     * 获取插件的后台列表
     */
    public function getAdminList()
    {
        $admin = array();
        $db_addons = $this->where("status=1 AND has_adminlist=1")->field('title,name')->select();
        if ($db_addons) {
            foreach ($db_addons as $value) {
                $admin[] = array('title' => $value['title'], 'url' => "Addons/adminList?name={$value['name']}");
            }
        }
        return $admin;
    }
}
