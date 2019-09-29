<?php
/**
 * 所属项目 MuuCmf T5
 * 开发者: 大蒙
 */
namespace app\common\model;

use think\Model;
use think\Db;

class Module extends Model
{
    protected $tokenFile = '/Info/token.ini';
    protected $moduleName = '';

    /**
     * 获取已安装模块分页列表
     */
    public function getListByPage($map,$order='create_time desc',$field='*',$r=20)
    {
        $list = $this->where($map)->order($order)->field($field)->paginate($r,false,['query'=>request()->param()]);

        foreach ($list as &$val) {
            //如果icon图片存在
            if(file_exists(PUBLIC_PATH . '/static/' . $val['name'] . '/images/icon.png')){
                $val['icon_photo'] = '/static/'. $val['name'] .'/images/icon.png';
            }elseif(file_exists(PUBLIC_PATH . '/static/' . $val['name'] . '/icon.png')){
                $val['icon_photo'] = '/static/'. $val['name'] .'/icon.png';
            }else{
                $val['icon_photo'] = '/static/admin/images/module_default_icon.png';
            }
        }
        unset($val);

        return $list;
    }
    /**获取全部的模块信息
     * @return array|mixed
     */
    public function getAll($is_installed = '')
    {   
        $dir = $this->getDir(APP_PATH);

        foreach($dir as $k=>$v){
            if($v == '.htaccess' || $v == 'extra' || $v == 'lang'){
              unset($dir[$k]);
            }
        }

        foreach ($dir as $subdir) {
            if (file_exists(APP_PATH . '/' . $subdir . '/info/info.php') && $subdir != '.' && $subdir != '..') {

                $info = $this->getModule($subdir);
                
                if ($is_installed == 1 && $info['is_setup'] == 0) {
                    continue;
                }
                $this->moduleName = $info['name'];
                //如果icon图片存在
                //图标所在位置为模块静态目录跟下（推荐）
                if(file_exists(PUBLIC_PATH . '/static/' . $info['name'] . '/images/icon.png')){
                    $info['icon'] = '/static/'. $info['name'] .'/images/icon.png';
                }elseif(file_exists(PUBLIC_PATH . '/static/' . $info['name'] . '/icon.png')){
                    $info['icon'] = '/static/'. $info['name'] .'/icon.png';
                }else{
                    $info['icon'] = '/static/admin/images/module_default_icon.png';
                }
                
                $module[] = $info;
            }
        }

        return $module;
    }

    /**
     * 获取application目录下模块
     *
     * @param      <type>  $dir    The dir
     *
     * @return     <type>  The dir.
     */
    public function getDir($dir) {
        $dirArray[]=NULL;
        if (false != ($handle = opendir ( $dir ))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".."&&!strpos($file,".")) {
                    $dirArray[$i]=$file;
                    $i++;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        return $dirArray;
    }

    /**
     * 重新通过文件来同步模块
     */
    public function reload()
    {
        $modules = collection($this->select())->toArray();
        $info = [];
        foreach ($modules as $m) {
            if (file_exists(APP_PATH . '/' . $m['name'] . '/info/info.php') || file_exists(APP_PATH . '/' . $m['name'] . '/info/Info.php')) {
                $info[] = array_merge($m, $this->getInfo($m['name']));
            } 
        }
        
        $this->saveAll($info);

        $this->cleanModulesCache();
    }

    /**重置单个模块信息
     * @param $name
     */
    public function reloadModule($name)
    {
        $module = $this->where(['name' => $name])->find();
        if (empty($module)) {
            $this->error = lang('_MODULE_INFORMATION_DOES_NOT_EXIST_WITH_PERIOD_');
            return false;
        } else {
            if (file_exists(APP_PATH . '/' . $module['name'] . '/info/info.php') || file_exists(APP_PATH . '/' . $module['name'] . '/info/Info.php')) {
                $info = array_merge($module, $this->getInfo($module['name']));
                $this->save($info);
                $this->cleanModuleCache($name);
                return true;
            }
        }
    }

    /**
     * 检查是否可以访问模块，被用于控制器初始化
     * @param $name
     */
    public function checkCanVisit($name)
    {
        $modules = $this->getAll();

        foreach ($modules as $m) {
            if (isset($m['is_setup']) && $m['is_setup'] == 0 && $m['name'] == ucfirst($name)) {
                header("Content-Type: text/html; charset=utf-8");
                exit('您所访问的模块未安装，禁止访问，请管理员到后台扩展-本地-模块中安装。');
            }
        }

    }

    /**检查模块是否已经安装
     * @param $name
     * @return bool
     */
    public function checkInstalled($name)
    {
        $modules = $this->getAll();

        foreach ($modules as $m) {
            if ($m['name'] == $name && $m['is_setup']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 清理全部模块的缓存
     */
    public function cleanModulesCache()
    {
        $modules = $this->getAll();

        foreach ($modules as $m) {
            $this->cleanModuleCache($m['name']);
        }
        cache('module_all', null);
        cache('admin_modules', null);
        cache('ALL_MESSAGE_SESSION',null);
        cache('ALL_MESSAGE_TPLS',null);
    }

    /**清理某个模块的缓存
     * @param $name 模块名
     */
    public function cleanModuleCache($name)
    {
        cache('common_module_' . strtolower($name), null);

    }

    /**卸载模块
     * @param $id 模块ID
     * @param int $withoutData 0.不清理数据 1.清理数据
     * @return bool
     */
    public function uninstall($id, $withoutData = 1)
    {
        $module = $this->get($id);
        $module = $module->toArray();

        if (!$module || $module['is_setup'] == 0) {
            $this->error = lang('_MODULE_DOES_NOT_EXIST_OR_IS_NOT_INSTALLED_WITH_PERIOD_');
            return false;
        }
        $this->cleanMenus($module['name']);
        $this->cleanAuthRules($module['name']);
        $this->cleanAction($module['name']);
        $this->cleanActionLimit($module['name']);

        if ($withoutData == 0) {
            //如果不保留数据
            if (file_exists(APP_PATH . '/' . $module['name'] . '/info/cleanData.sql')) {
                $uninstallSql = APP_PATH . '/' . $module['name'] . '/info/cleanData.sql';

                $uninstallSql = file_get_contents($uninstallSql);
                if(empty($uninstallSql) || $uninstallSql = ''){
                    $this->cleanModulesCache();
                    return true;
                }
                $uninstallSql = str_replace("\r", "\n", $uninstallSql);
                $uninstallSql = explode(";\n", $uninstallSql);
                $res = Db::execute($uninstallSql);
                if ($res === false) {
                    $this->error = lang('_CLEAN_UP_THE_MODULE_DATA_AND_ERROR_MESSAGE_WITH_COLON_') . $res['error_code'];
                    return false;
                }
            }
            //兼容老的卸载方式，执行一边uninstall.sql
            if (file_exists(APP_PATH . '/' . $module['name'] . '/info/uninstall.sql')) {
                $uninstallSql = APP_PATH . '/' . $module['name'] . '/info/uninstall.sql';

                $uninstallSql = file_get_contents($uninstallSql);
                if(empty($uninstallSql) || $uninstallSql = ''){
                    $this->cleanModulesCache();
                    return true;
                }
                $uninstallSql = str_replace("\r", "\n", $uninstallSql);
                $uninstallSql = explode(";\n", $uninstallSql);
                $res = Db::execute($uninstallSql);
                if ($res === false) {
                    $this->error = lang('_CLEAN_UP_THE_MODULE_DATA_AND_ERROR_MESSAGE_WITH_COLON_') . $res['error_code'];
                    return false;
                }
            }
        }
        $module['is_setup'] = 0;
        $this->save($module,['id'=>$id]);

        $this->cleanModulesCache();
        return true;
    }

    /**通过模块名来获取模块信息
     * @param $name 模块名
     * @return array|mixed
     */
    public function getModule($name)
    {
        if($name=='admin'){
            return false;
        }

        $info = $this->where(['name'=>$name])->find();

        if(file_exists(PUBLIC_PATH . '/static/' . $info['name'] . '/images/icon.png')){
            $info['icon'] = '/static/'. $info['name'] .'/images/icon.png';
        }elseif(file_exists(PUBLIC_PATH . '/static/' . $info['name'] . '/icon.png')){
            $info['icon'] = '/static/'. $info['name'] .'/icon.png';
        }else{
            $info['icon'] = '/static/admin/images/module_default_icon.png';
        }

        return $info;
    }

    /**通过ID获取模块信息
     * @param $id
     * @return array|mixed
     */
    public function getModuleById($id)
    {
        $module = $this->where(['id' => $id])->find();
        if ($module === false || $module == null) {
            $m = $this->getInfo($module['name']);
            if ($m != array()) {
                if ($m['can_uninstall']) {
                    $m['is_setup'] = 0;//默认设为已安装，防止已安装的模块反复安装。
                } else {
                    $m['is_setup'] = 1;
                }
                $m['id'] = $this->add($m);
                $m['token'] = $this->getToken($m['name']);
                return $m;
            }

        } else {
            $module['token'] = $this->getToken($module['name']);
            return $module;
        }
    }


    /**
     * 检查某个模块是否已经是安装的状态
     * @param $name
     * @return bool
     */
    public function isInstalled($name)
    {
        $module = $this->getModule($name);
        if ($module['is_setup']) {
            return true;
        } else {
            return false;
        }
    }

    /**安装某个模块
     * @param $id
     * @return bool
     */
    public function install($id)
    {
        $log = '';
        if ($id != 0) {
            $module = $this->find($id);
        } else {
            $aName = input('get.name','','text');
            if(empty($aName)){
                return false;
            }
            $module = $this->getModule($aName);
        }

        $module = $module->toArray();
        if ($module['is_setup'] == 1) {
            $this->error = lang('_MODULE_INSTALLED_WITH_PERIOD_');
            return false;
        }
        
        if (file_exists(APP_PATH . $module['name'] . DS . 'info' .DS. 'guide.json')) {

            //如果存在guide.json
            $guide = file_get_contents(APP_PATH . $module['name'] . DS . 'info' . DS . 'guide.json');
            $data = json_decode($guide, true);

            //导入菜单项,menu
            $menu = json_decode($data['menu'], true);
            
            if (!empty($menu)) {
                $this->cleanMenus($module['name']);
                if ($this->addMenus($menu[0]) == true) {
                    $log .= '&nbsp;&nbsp;>菜单成功安装;<br/>';
                }
            }

            //导入前台权限,auth_rule
            $auth_rule = json_decode($data['auth_rule'], true);
            if (!empty($auth_rule)) {
                $this->cleanAuthRules($module['name']);
                if ($this->addAuthRule($auth_rule)) {
                    $log .= '&nbsp;&nbsp;>权限成功导入。<br/>';
                }
                //设置默认的权限
                $default_rule = json_decode($data['default_rule'], true);
                if ($this->addDefaultRule($default_rule, $module['name'])) {
                    $log .= '&nbsp;&nbsp;>默认权限设置成功。<br/>';
                }
            }

            //导入
            $action = json_decode($data['action'], true);
            if (!empty($action)) {
                $this->cleanAction($module['name']);
                if ($this->addAction($action)) {
                    $log .= '&nbsp;&nbsp;>行为成功导入。<br/>';
                }
            }

            $action_limit = json_decode($data['action_limit'], true);
            if (!empty($action_limit)) {
                $this->cleanActionLimit($module['name']);
                if ($this->addActionLimit($action_limit)) {
                    $log .= '&nbsp;&nbsp;>行为限制成功导入。<br/>';
                }
            }

            if (file_exists(APP_PATH . '/' . $module['name'] . '/info/install.sql')) {
                $install_sql = APP_PATH . '/' . $module['name'] . '/info/install.sql';

                $install_sql = file_get_contents($install_sql);
                $install_sql = str_replace("\r", "\n", $install_sql);
                $install_sql = explode(";\n", $install_sql);
                //系统配置表前缀
                $prefix = config('database.prefix');

                foreach ($install_sql as $value) {
                    
                    $value = trim($value);
                    if (empty($value)) continue;
                    if (strpos($value,'CREATE TABLE')) {//创建表
                        //获取表名
                        $name = preg_replace("/[\s\S]*CREATE TABLE IF NOT EXISTS `(\w+)`[\s\S]*/", "\\1", $value);
                        //获取表前缀
                        $orginal = preg_replace("/[\s\S]*CREATE TABLE IF NOT EXISTS `([a-zA-Z]+_)[\s\S]*/", "\\1", $value);
                        //替换表前缀
                        $value = str_replace(" `{$orginal}", " `{$prefix}", $value);
                        
                        $msg = "创建数据表{$name}";
                        if (false !== Db::execute($value)) {
                            $log .= '&nbsp;&nbsp;>'.$msg . '...成功;';
                        } else {
                            $log .= '&nbsp;&nbsp;>'.$msg . '...失败;';
                        }
                    } else {//写入数据
                        
                        //获取表名
                        $name = preg_replace("/[\s\S]*INSERT INTO `(\w+)`[\s\S]*/", "\\1", $value);
                        //获取表前缀
                        $orginal = preg_replace("/[\s\S]*INSERT INTO `([a-zA-Z]+_)[\s\S]*/", "\\1", $value);
                        //替换表前缀
                        $value = str_replace(" `{$orginal}", " `{$prefix}", $value);
                        //写入前清空
                        Db::execute("TRUNCATE TABLE `{$name}`;");
                        
                        Db::execute($value);
                    }
                }
            }
        }
        $module['is_setup'] = 1;
        $module['auth_role'] = input('post.auth_role','','text');

        $rs = $this->save($module,['id'=>$module['id']]);
        if ($rs === false) {
            $this->error = lang('_MODULE_INFORMATION_MODIFICATION_FAILED_WITH_PERIOD_');
            return false;
        }
        $this->cleanModulesCache();//清除全站缓存
        $this->error = $log;
        return true;
    }

    /*——————————————————————————私有域—————————————————————————————*/

    /**获取模块的相对目录
     * @param $file
     * @return string
     */
    private function getRelativePath($file)
    {
        return APP_PATH . $this->moduleName . $file;
    }

    private function addDefaultRule($default_rule, $module_name)
    {
        foreach ($default_rule as $v) {
            $rule = Db::name('AuthRule')->where(['module' => $module_name, 'name' => $v])->find();
            if ($rule) {
                $default[] = $rule;
            }
        }
        $auth_id = getSubByKey($default, 'id');
        if ($auth_id) {
            $groups = Db::name('AuthGroup')->select();
            foreach ($groups as $g) {
                $old = explode(',', $g['rules']);
                $new = array_merge($old, $auth_id);
                $g['rules'] = implode(',', $new);
                Db::name('AuthGroup')->save($g);
            }
        }
        return true;
    }

    private function addAction($action)
    {
        foreach ($action as $v) {
            unset($v['id']);
            Db::name('Action')->insert($v);
        }
        return true;
    }

    private function addActionLimit($action)
    {
        foreach ($action as $v) {
            unset($v['id']);
            Db::name('ActionLimit')->insert($v);
        }
        return true;
    }

    private function addAuthRule($auth_rule)
    {
        foreach ($auth_rule as $v) {
            unset($v['id']);
            Db::name('AuthRule')->insert($v);
        }
        return true;
    }

    private function cleanActionLimit($module_name)
    {
        $db_prefix = config('database.prefix');
        $sql = "DELETE FROM `{$db_prefix}action_limit` where `module` = '" . $module_name . "'";
        Db::execute($sql);
    }

    private function cleanAction($module_name)
    {
        $db_prefix = config('database.prefix');
        $sql = "DELETE FROM `{$db_prefix}action` where `module` = '" . $module_name . "'";
        Db::execute($sql);
    }

    private function cleanAuthRules($module_name)
    {
        $db_prefix = config('database.prefix');
        $sql = "DELETE FROM `{$db_prefix}auth_rule` where `module` = '" . $module_name . "'";
        Db::execute($sql);
    }

    private function cleanMenus($module_name)
    {
        $db_prefix = config('database.prefix');
        $sql = "DELETE FROM `{$db_prefix}menu` where `url` like '" . $module_name . "/%'";
        Db::execute($sql);
    }
    /**
     * 写入模块菜单
     * @param [type] $menu [description]
     */
    private function addMenus($menu)
    {
        Db::name('Menu')->strict(false)->insert($menu);

        //$menu['id'] = $id;
        if (!empty($menu['_']))
            foreach ($menu['_'] as $v) {
                $this->addMenus($v);
            }
        return true;
    }

    /**
     * 获取模块信息
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    private function getInfo($name)
    {
        if (file_exists(APP_PATH . '/' . $name . '/info/info.php')) {
            $module = require(APP_PATH . '/' . $name . '/info/info.php');
            return $module;
        } else {
            return [];
        }

    }

    /**
     * 获取文件列表
     */
    private function getFile($folder)
    {
        //打开目录
        $fp = opendir($folder);
        //阅读目录
        while (false != $file = readdir($fp)) {
            //列出所有文件并去掉'.'和'..'
            if ($file != '.' && $file != '..') {
                //$file="$folder/$file";
                $file = "$file";

                //赋值给数组
                $arr_file[] = $file;

            }
        }
        //输出结果
        if (is_array($arr_file)) {
            while (list($key, $value) = each($arr_file)) {
                $files[] = $value;
            }
        }
        //关闭目录
        closedir($fp);
        return $files;
    }

} 