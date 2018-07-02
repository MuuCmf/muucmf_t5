<?php
namespace app\common\controller;

use think\Config;
use think\View;
/**
 * 插件类
 */
abstract class Addons{

    //视图实例对象
    protected $view = null;

    public $info                =   [];
    public $addon_path          =   '';
    public $config_file         =   '';
    public $custom_config       =   '';
    public $admin_list          =   [];
    public $custom_adminlist    =   '';
    public $access_url          =   [];

    public function __construct(){
        // 获取当前插件目录
        $this->addon_path = ADDON_PATH . $this->getName(). DS;

        $view_replace_str = Config::get('view_replace_str');

        $view_replace_str['__ADDONROOT__'] = $this->addon_path;

        Config::set('view_replace_str', $view_replace_str);

        // 初始化视图模型
        $config = ['view_path' => $this->addons_path];
        $config = array_merge(Config::get('template'), $config);

        $this->view = new View($config, Config::get('view_replace_str'));

        if(is_file($this->addon_path.'config.php')){
            $this->config_file = $this->addon_path . 'config.php';
        }

        // 控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }
    
    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Action
     */
    final protected function assign($name,$value='') {
        $this->view->assign($name,$value);
        return $this;
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $template 模板文件名或者内容
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @return mixed
     * @throws \Exception
     */
    final public function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        if (!is_file($template)) {
            $template = '/' . $template;
        }
        // 关闭模板布局
        $this->view->engine->layout(false);

        echo $this->view->fetch($template, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @return mixed
     */
    final public function display($content, $vars = [], $replace = [], $config = [])
    {
        // 关闭模板布局
        $this->view->engine->layout(false);

        echo $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array $vars 模板输出变量
     * @return mixed
     */
    final public function show($content, $vars = [])
    {
        // 关闭模板布局
        $this->view->engine->layout(false);

        echo $this->view->fetch($content, $vars, [], [], true);
    }

    /**
     * 获取当前模块名
     * @return string
     */
    final public function getName(){

        $class = get_class($this);
        return substr($class,strrpos($class, '\\')+1, -5);
    }

    /**
     * 检查基础配置信息是否完整
     * @return bool
     */
    final public function checkInfo(){
        $info_check_keys = ['name','title','description','status','author','version'];
        foreach ($info_check_keys as $value) {
            if(!array_key_exists($value, $this->info))
                return false;
        }
        return true;
    }

    /**
     * 获取插件的配置数组
     */
    final public function getConfig($name=''){
        static $_config = array();
        if(empty($name)){
            $name = $this->getName();
        }
        if(isset($_config[$name])){
            return $_config[$name];
        }
        $config =   array();
        $map['name']    =   $name;
        $map['status']  =   1;
        $config  =   Db::name('Addons')->where($map)->value('config');
        if($config){
            $config   =   json_decode($config, true);
        }else{
            $temp_arr = include $this->config_file;
            foreach ($temp_arr as $key => $value) {
                if($value['type'] == 'group'){
                    foreach ($value['options'] as $gkey => $gvalue) {
                        foreach ($gvalue['options'] as $ikey => $ivalue) {
                            $config[$ikey] = $ivalue['value'];
                        }
                    }
                }else{
                    $config[$key] = $temp_arr[$key]['value'];
                }
            }
        }
        $_config[$name]     =   $config;
        return $config;
    }

    /**初始化钩子的方法，防止钩子不存在的情况发生
     * @param $name
     * @param $description
     * @param int $type
     * @return bool
     */
    public function initHook($name,$description,$type=1){
        $hook=Db::name('hooks')->where(['name'=>$name])->find();
        if(!$hook){
            $hook['name']=$name;
            $hook['description']=$description;
            $hook['type']=$type;
            $hook['update_time']=time();
            $hook['addons']=$this->getName();
            $result=Db::name('hooks')->insert($hook);

            if($result===false){
                return false;
            }else{
                return true;
            }
        }
        return true;
    }

    /**
     * 获取当前错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();
}
