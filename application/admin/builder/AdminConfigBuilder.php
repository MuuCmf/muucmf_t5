<?php
namespace app\admin\builder;

use think\Db;
use think\Request;

class AdminConfigBuilder extends AdminBuilder
{
    private $_title;
    private $_suggest;
    private $_keyList = array();
    private $_data = array();
    private $_buttonList = array();
    private $_savePostUrl = array();
    private $_group = array();
    private $_callback = null;

    public function title($title)
    {
        $this->_title = $title;
        $this->meta_title = $title;
        return $this;
    }

    /**
     * suggest  页面标题边上的提示信息
     * @param $suggest
     * @return $this
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function suggest($suggest)
    {
        $this->_suggest = $suggest;
        return $this;
    }

    public function callback($callback)
    {
        $this->_callback = $callback;
        return $this;
    }

    /**键，一般用于内部调用
     * @param      $name
     * @param      $title
     * @param null $subtitle
     * @param      $type
     * @param null $opt
     * @return $this
     */
    public function key($name, $title, $subtitle = null, $type, $opt = null)
    {
        $key = ['name' => $name, 'title' => $title, 'subtitle' => $subtitle, 'type' => $type, 'opt' => $opt];
        $this->_keyList[] = $key;
        return $this;
    }

    /**只读文本
     * @param      $name
     * @param      $title
     * @param null $subtitle
     * @return AdminConfigBuilder
     * @auth 陈一枭
     */
    public function keyHidden($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'hidden');
    }

    /**只读文本
     * @param      $name
     * @param      $title
     * @param null $subtitle
     * @return AdminConfigBuilder
     * @auth 陈一枭
     */
    public function keyReadOnly($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'readonly');
    }
    /**只读文本框
    *
    */
    public function keyReadOnlyText($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'readonlytext');
    }
    /**
     * **只读纯HTML
     * @param  [type] $name     [description]
     * @param  [type] $title    [description]
     * @param  [type] $subtitle [description]
     * @return [type]           [description]
     * @auth 大蒙<59262424@qq.com>
     */
    public function keyReadOnlyHtml($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'readonlyhtml');
    }

    /**文本输入框
     * @param      $name
     * @param      $title
     * @param null $subtitle
     * @return AdminConfigBuilder
     * @auth 陈一枭
     */
    public function keyText($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'text');
    }

    /**颜色选择器
     * @param $name
     * @param $title
     * @param null $subtitle
     * @return $this
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function keyColor($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'colorPicker');
    }

    public function keyIcon($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'icon');
    }

    public function keyLabel($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'label');
    }

    public function keyTextArea($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'textarea');
    }

    public function keyInteger($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'integer');
    }

    public function keyUid($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'uid');
    }

    public function keyStatus($name = 'status', $title = '状态', $subtitle = null)
    {
        $map = array(-1 => lang('_DELETE_'), 0 => lang('_DISABLE_'), 1 => lang('_ENABLE_'), 2 => lang('_UNAUDITED_'));
        return $this->keySelect($name, $title, $subtitle, $map);
    }

    public function keySelect($name, $title, $subtitle = null, $options)
    {
        return $this->key($name, $title, $subtitle, 'select', $options);
    }

    public function keyRadio($name, $title, $subtitle = null, $options)
    {
        return $this->key($name, $title, $subtitle, 'radio', $options);
    }

    public function keyCheckBox($name, $title, $subtitle = null, $options)
    {
        return $this->key($name, $title, $subtitle, 'checkbox', $options);
    }
    /**
     * 调用不同的富文本编辑器
     * @param  [type] $name     字段
     * @param  [type] $title    标题
     * @param  [type] $subtitle 标题描述
     * @param  string $type     目前支持ueditor\wangeditou，默认editor
     * @param  string $config   配置项，需参考相应编辑器文档设置
     * @param  string $style    样式 如：height:200px;width:200px
     * @param  string $param    预留参数
     * @param  string $width    [description]
     * @return [type]           [description]
     */
    public function keyEditor($name, $title, $subtitle = null, $type = 'ueditor', $config = '', $style = '',$param='', $width='100%')
    {
        if(empty($type) || $type=='' || $type!='wangeditor'){
            $type='ueditor';
        }
        //兼容老版
        if(is_array($config)){
            $config = '';
        }
        /*
        if (empty($config)) {
            $toolbars = "toolbars:[['source','|','bold','italic','underline','fontsize','forecolor','justifyleft','fontfamily','|','map','emotion','insertimage','insertcode']]";
        }
        if ($config == 'all') {
            $toolbars = 'all';
        }*/
        $key = array('name' => $name, 'title' => $title, 'subtitle' => $subtitle, 'config' => $config, 'style' => $style, 'param'=>$param , 'width'=>$width, 'type' => $type);
        $this->_keyList[] = $key;
        return $this;
    }

    /**
     * 日期选择器：支持三种类型
     * @param $name
     * @param $title
     * @param null $subtitle
     * @param string $type 类型：支持（time）（datetime，默认）(date)
     * @return $this
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function keyTime($name, $title, $subtitle = null,$type='datetime')
    {
        return $this->key($name, $title, $subtitle, $type);
    }

    public function keyCreateTime($name = 'create_time', $title = '创建时间', $subtitle = null)
    {
        return $this->keyTime($name, $title, $subtitle);
    }

    public function keyBool($name, $title, $subtitle = null)
    {
        $map = array(1 => lang('_YES_'), 0 => lang('_NO_'));
        return $this->keyRadio($name, $title, $subtitle, $map);
    }

    public function keySwitch($name, $title, $subtitle = null)
    {
        $map = array(1 => lang('_OPEN_'), 0 => lang('_CLOSE_'));
        return $this->keyRadio($name, $title, $subtitle, $map);
    }

    public function keyUpdateTime($name = 'update_time', $title = '修改时间', $subtitle = null)
    {
        return $this->keyTime($name, $title, $subtitle);
    }

    public function keyKanban($name, $title, $subtitle = null)
    {

        return $this->key($name, $title, $subtitle, 'kanban');
    }

    public function keyTitle($name = 'title', $title = '标题', $subtitle = null)
    {
        return $this->keyText($name, $title, $subtitle);
    }

    public function keyId($name = 'id', $title = '编号', $subtitle = null)
    {
        return $this->keyReadOnly($name, $title, $subtitle);
    }

    public function keyMultiUserGroup($name, $title, $subtitle = null)
    {
        $options = $this->readUserGroups();
        return $this->keyCheckBox($name, $title, $subtitle, $options);
    }

    /**单文件上传
     * @param $name
     * @param $title
     * @param null $subtitle
     */
    public function keySingleFile($name, $title, $subtitle = null){
        return  $this->key($name,$title,$subtitle,'singleFile');
    }

    /**多文件上传
     * @param $name
     * @param $title
     * @param null $subtitle
     */
    public function keyMultiFile($name, $title, $subtitle = null){
        return   $this->key($name,$title,$subtitle,'multiFile');
    }



    public function keySingleImage($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'singleImage');
    }

    public function keyMultiImage($name, $title, $subtitle = null, $limit = '')
    {
        return $this->key($name, $title, $subtitle, 'multiImage', $limit);
    }

    public function keySingleUserGroup($name, $title, $subtitle = null)
    {
        $options = $this->readUserGroups();
        return $this->keySelect($name, $title, $subtitle, $options);
    }

    /** 添加城市选择（需安装城市联动插件）
     * @param array $name
     * @param $title
     * @param $subtitle
     * @return AdminConfigBuilder
     * @author LaoYang
     * @author @MingYangliu <xint5288@126.com>
     */
    public function keyCity($name=array('province','city','district'),$title, $subtitle)
    {
        //修正在编辑信息时无法正常显示已经保存的地区信息
        return $this->key($name, $title, $subtitle, 'city');
    }


    /**
     * 增加数据时通过列表页选择相应的关联数据ID  -_-。sorry！表述不清楚..
     * @param  unknown $name 字段名
     * @param  unknown $title 标题
     * @param  string $subtitle 副标题（说明）
     * @param  unknown $url 选择数据的列表页地址，U方法地址'index/index'
     * @return $this
     * @author @MingYangliu <xint5288@126.com>
     */
    public function keyDataSelect($name, $title, $subtitle = null, $url)
    {
        $urls = Url($url, array('inputid' => $name));
        return $this->key($name, $title, $subtitle, 'dataselect', $urls);
    }

    public function button($title, $attr = array())
    {
        $this->_buttonList[] = array('title' => $title, 'attr' => $attr);
        return $this;
    }

    public function buttonSubmit($url = '', $title = '确定')
    {
        if ($url == '') {
            $url = Url(request()->action(),$_GET);
        }
        $this->savePostUrl($url);

        $attr = array();
        $attr['class'] = "btn submit-btn ajax-post btn-success";
        $attr['id'] = 'submit';
        $attr['type'] = 'submit';
        $attr['target-form'] = 'form-horizontal';
        return $this->button($title, $attr);
    }

    public function buttonBack($title = '返回')
    {
        $attr = array();
        $attr['onclick'] = 'javascript:history.back(-1);return false;';
        $attr['class'] = 'btn btn-return';
        return $this->button($title, $attr);
    }

    public function buttonLink($title='按钮',$attr){
        $attr['onclick'] = 'javascript:location.href=\''.$attr['href'].'\';return false;';
        return $this->button($title, $attr);
    }
    public function data($list)
    {
        $this->_data = $list;
        return $this;
    }

    public function savePostUrl($url)
    {
        if ($url) {
            $this->_savePostUrl = $url;
        }
    }

    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
    {

        //将数据融入到key中
        foreach ($this->_keyList as &$e) {
            if ($e['type'] == 'multiInput') {
                $e['name'] = explode('|', $e['name']);
            }

            //修正在编辑信息时无法正常显示已经保存的地区信息/***修改的代码****/
            if (is_array($e['name'])) {
                $i = 0;
                $n = count($e['name']);
                while ($n > 0) {
                    $e['value'][$i] = $this->_data[$e['name'][$i]];
                    $i++;
                    $n--;
                }
            } else {
                //修复未定义数组下标提示
                empty($this->_data[$e['name']]) && $this->_data[$e['name']] = null;
                $e['value'] = $this->_data[$e['name']];
            }
        }

        //编译按钮的html属性
        
        foreach ($this->_buttonList as &$button) {
            $button['attr'] = $this->compileHtmlAttr($button['attr']);
        }

        //显示页面
        $this->assign('group', $this->_group);
        $this->assign('title', $this->_title);
        $this->assign('suggest', $this->_suggest);
        $this->assign('keyList', $this->_keyList);
        $this->assign('buttonList', $this->_buttonList);
        $this->assign('savePostUrl', $this->_savePostUrl);

        parent::display('admin_config');
    }

    /**
     * keyChosen  多选菜单
     * @param $name
     * @param $title
     * @param null $subtitle
     * @param $options
     * @return $this
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function keyChosen($name, $title, $subtitle = null, $options)
    {
        // 解析option数组
        if (key($options) === 0) {
            if (!is_array(reset($options))) {
                foreach ($options as $key => &$val) {
                    $val = array($val, $val);
                }
                unset($key, $val);
            }
        } else {
            foreach ($options as $key => &$val) {
                foreach ($val as $k => &$v) {
                    if (!is_array($v)) {
                        $v = array($v, $v);
                    }
                }
                unset($k, $v);
            }
            unset($key, $val);
        }
        return $this->key($name, $title, $subtitle, 'chosen', $options);
    }


    /**
     * keyMultiInput  输入组组件
     * @param $name
     * @param $title
     * @param $subtitle
     * @param $config
     * @param null $style
     * @return $this
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function keyMultiInput($name, $title, $subtitle, $config, $style = null)
    {
        empty($style) && $style = 'width:400px;';
        $key = array('name' => $name, 'title' => $title, 'subtitle' => $subtitle, 'type' => 'multiInput', 'config' => $config, 'style' => $style);
        $this->_keyList[] = $key;
        return $this;
    }

    /**插入配置分组
     * @param       $name 组名
     * @param array $list 组内字段列表
     * @return $this
     * @auth 肖骏涛
     */
    public function group($name, $list = array())
    {
        !is_array($list) && $list = explode(',', $list);
        $this->_group[$name] = $list;
        return $this;
    }

    public function groups($list = array())
    {
        foreach ($list as $key => $v) {
            $this->_group[$key] = is_array($v) ? $v : explode(',', $v);
        }
        return $this;
    }


    /**
     * 自动处理配置存储事件，配置项必须全大写
     */
    public function handleConfig()
    {
        if (request()->isPost()) {
            $success = false;
            $configModel = Db::name('config');
            foreach (input() as $k => $v) {
                $config['name'] = '_' . strtoupper(request()->controller()) . '_' . strtoupper($k);
                $config['type'] = 0;
                $config['title'] = '';
                $config['group'] = 0;
                $config['extra'] = '';
                $config['remark'] = '';
                $config['create_time'] = time();
                $config['update_time'] = time();
                $config['status'] = 1;
                $config['value'] = is_array($v) ? implode(',', $v) : $v;
                $config['sort'] = 0;

                //查询是否存在
                $this_conf = $configModel->where(['name' => $config['name']])->find();

                if($this_conf) {
                    //$config['id'] = $this_conf['id'];
                    $configModel->where(['id' => $this_conf['id']])->update($config);
                    $success = 1;
                }else{
                    $configModel->insert($config);
                    $success = 1;
                }
                $tag = 'conf_' . strtoupper(request()->controller()) . '_' . strtoupper($k);
                cache($tag, null);
            }
            if ($success) {
                if ($this->_callback) {
                    $str = $this->_callback;
                    controller(request()->controller())->$str(input(''));
                }
                //当前模块url地址
                $request= Request::instance();
                $module_name=$request->module();
                $controller_name=$request->controller();
                $action=$request->action();
                $active_url=$module_name.'/'.$controller_name.'/'.$action;

                header('Content-type: application/json');
                exit(json_encode(['info' => lang('_SUCCESS_CONF_SAVE_').lang('_PERIOD_'), 'status' => 1, 'url' => Url($active_url)]));
            } else {
                header('Content-type: application/json');
                exit(json_encode(['info' => lang('_FAIL_CONF_SAVE_').lang('_PERIOD_'), 'status' => 0, 'url' => Url($active_url)]));
            }


        } else {
            $configs = Db::name('Config')->where(array('name' => array('like', '_' . strtoupper(request()->controller()) . '_' . '%')))->limit(999)->select();
            $data = array();
            foreach ($configs as $k => $v) {
                $key = str_replace('_' . strtoupper(request()->controller()) . '_', '', strtoupper($v['name']));
                $data[$key] = $v['value'];
            }
            return $data;
        }
    }

    private function readUserGroups()
    {
        $list = Db::name('AuthGroup')->where(array('status' => 1))->order('id asc')->select();
        $result = array();
        foreach ($list as $group) {
            $result[$group['id']] = $group['title'];
        }
        return $result;
    }

    /**
     * parseKanbanArray  解析看板数组
     * @param $data
     * @param array $item
     * @param array $default
     * @return array|mixed
     */
    public function parseKanbanArray($data, $item = array(), $default = array())
    {
        if (empty($data)) {
            $head = reset($default);
            if (!array_key_exists("items", $head)) {
                $temp = array();
                foreach ($default as $k => $v) {
                    $temp[] = array('id' => $k, 'title' => $k, 'items' => $v);
                }
                $default = $temp;
            }
            $result = $default;
        } else {
            $data = json_decode($data, true);

            $item_d = getSubByKey($item, 'id');
            $all = array();
            foreach ($data as $key => $v) {
                $data_id = getSubByKey($v['items'], 'id');
                $data_d[$key] = $v;
                unset($data_d[$key]['items']);
                $data_d[$key]['items'] = $data_id ? $data_id : array();
                $all = array_merge($all, $data_id);
            }
            unset($v);
            foreach ($item_d as $val) {
                if (!in_array($val, $all)) {
                    $data_d[0]['items'][] = $val;
                }
            }
            unset($val);
            foreach ($all as $v) {
                if (!in_array($v, $item_d)) {
                    foreach ($data_d as $key => $val) {
                        $key_search = array_search($v, $val['items']);
                        if (!is_bool($key_search)) {
                            unset($data_d[$key]['items'][$key_search]);
                        }
                    }
                    unset($val);
                }
            }
            unset($v);
            $item_t = array();
            foreach ($item as $val) {

                $item_t[$val['id']] = $val['title'];
            }
            unset($v);

            foreach ($data_d as &$v) {
                foreach ($v['items'] as &$val) {
                    $t = $val;
                    $val = array();
                    $val['id'] = $t;
                    $val['title'] = $item_t[$t];
                }
                unset($val);
            }
            unset($v);

            $result = $data_d;
        }
        return $result;

    }

    public function setDefault($data, $key, $value)
    {
        $data[$key] = $data[$key]!=null ? $data[$key] : $value;
        return $data;
    }

    public function keyDefault($key, $value)
    {
        $data = $this->_data;
        empty($data[$key]) && $data[$key]=null;
        $data[$key] = $data[$key]!==null ? $data[$key] : $value;
        $this->_data = $data;
        return $this;
    }

    /**
     * groupLocalComment
     * @param $group_name    组名
     * @param $mod    mod名。path的第二个参数。
     * @return $this
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function groupLocalComment($group_name,$mod){
        $mod = strtoupper($mod);
        $this->keyDefault($mod.'_LOCAL_COMMENT_CAN_GUEST',1);
        $this->keyDefault($mod.'_LOCAL_COMMENT_ORDER',0);
        $this->keyDefault($mod.'_LOCAL_COMMENT_COUNT',10);
        $this->keyRadio($mod.'_LOCAL_COMMENT_CAN_GUEST', lang('_COMMENTS_ALLOW_VISITOR_IF_'), lang('_ALLOW_DEFAULT_'),array(0=>lang('_DISALLOW_'),1=>lang('_ALLOW_')))
            ->keyRadio($mod.'_LOCAL_COMMENT_ORDER',lang('_COMMENTS_SORT_'),lang('_DESC_DEFAULT_'),array(0=>lang('_DESC_'),1=>lang('_ASC_')))
            ->keyText($mod.'_LOCAL_COMMENT_COUNT',lang('_COMMENTS_PAGE_DISPLAY_COUNT_'),lang('_COMMENTS_PAGE_DISPLAY_COUNT_DESC'));
        $this->group($group_name, $mod.'_LOCAL_COMMENT_CAN_GUEST,'.$mod.'_LOCAL_COMMENT_ORDER,'.$mod.'_LOCAL_COMMENT_COUNT');
        return $this;
    }



    public function keyUserDefined($name,$title,$subtitle,$display='',$param=''){
        $this->assign('param',$param);
        $this->assign('name',$name);
        $html = $this->fetch($display);

        $key = array('name'=>$name, 'title' => $title, 'subtitle' => $subtitle, 'type' => 'userDefined', 'definedHtml' => $html);
        $this->_keyList[] = $key;
        return $this;
    }
    public function addCustomJs($script){
        $this->assign('myJs',$script);
    }








}