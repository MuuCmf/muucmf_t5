<?php
namespace app\admin\Controller;

use think\Db;

/**
 * 后台配置控制器
 */
class Menu extends Admin {
    /**
     * 后台菜单首页
     * @return none
     */
    public function index(){
        $pid  = input('get.pid','0','text');
        if($pid){
            $where['id'] = $pid;
            $data = Db::name('Menu')->where($where)->find();
            $this->assign('data',$data);
        }
        $type       =   config('CONFIG_GROUP_LIST');
        $all_menu   =   Db::name('Menu')->field('id,title')->select();
        $map['pid'] =   $pid;
        
        $list       =   Db::name("Menu")->where($map)->order('sort asc,id asc')->select();

        int_to_string($list,array('hide'=>array(1=>lang('_YES_'),0=>lang('_NOT_')),'is_dev'=>array(1=>lang('_YES_'),0=>lang('_NOT_'))));
        
        
        $this->assign('list',$list);

        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->setTitle(lang('_MENU_MANAGER_'));
        return $this->fetch();
    }

    /**
     * 新增菜单
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function add(){
        if(request()->isPost()){
            $Menu = Db::name('Menu');
            $data = input('');
            if($data){
                $id = $Menu->editData($data);
                if($id){
                    //记录行为
                    action_log('update_menu', 'Menu', $id, UID);
                    $this->success(lang('_SUCCESS_ADD_'), Cookie('__forward__'));
                } else {
                    $this->error(lang('_FAIL_ADD_'));
                }
            } else {
                $this->error($Menu->getError());
            }
        } else {
            $this->assign('info',array('pid'=>input('pid')));
            $menus = Db::name('Menu')->select();
            $menus = model('Common/Tree')->toFormatTree($menus);
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>lang('_MENU_TOP_'))), $menus);
            $this->assign('Modules',model('Module')->getAll());
            $this->assign('Menus', $menus);
            $this->setTitle(lang('_MENU_ADD_'));
            return $this->fetch('edit');
        }
    }

    /**
     * 编辑配置
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function edit($id = ''){
        header('Content-Type: text/html; charset=utf-8');
        if(request()->isPost()){
            $Menu = Db::name('Menu');
            $data = $Menu->create();
            if($data){
                if($Menu->save()!== false){
                    // S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_log('update_menu', 'Menu', $data['id'], UID);
                    $this->success(lang('_SUCCESS_UPDATE_'), Cookie('__forward__'));
                } else {
                    $this->error(lang('_FAIL_UPDATE_'));
                }
            } else {
                $this->error($Menu->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Menu')->field(true)->find($id);
            $menus = M('Menu')->field(true)->select();

            $menus = D('Common/Tree')->toFormatTree($menus,$title = 'title',$pk='id',$pid = 'pid',$root = '0');

            $menus = array_merge(array(0=>array('id'=>'','title_show'=>lang('_MENU_TOP_'))), $menus);

            $this->assign('Menus', $menus);
            $this->assign('Modules',D('Module')->getAll());
            if(false === $info){
                $this->error(lang('_ERROR_MENU_INFO_GET_'));
            }
            $this->assign('info', $info);
            $this->meta_title = lang('_MENU_BG_EDIT_');
            $this->display();
        }
    }

    /**
     * 删除后台菜单
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function del(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error(lang('_ERROR_DATA_SELECT_').lang('_EXCLAMATION_'));
        }
        //判断是否有下级菜单
        $res =  M('Menu')->where(array('pid' => array('in', $id)))->select();
        if($res){
            $this->error(lang('_DELETE_SUBMENU_'));
        }
        //开始移除菜单
        $map = array('id' => array('in', $id) );
        if(M('Menu')->where($map)->delete()){
            //记录行为
            action_log('update_menu', 'Menu', $id, UID);
            $this->success(lang('_SUCCESS_DELETE_'));
        } else {
            $this->error(lang('_FAIL_DELETE_'));
        }
    }
    

    public function toogleHide($id,$value = 1){
        $this->editRow('Menu', array('hide'=>$value), array('id'=>$id));
    }

    public function toogleDev($id,$value = 1){
        $this->editRow('Menu', array('is_dev'=>$value), array('id'=>$id));
    }

    public function importFile($tree = null, $pid=0){
        if($tree == null){
            $file = APP_PATH."Admin/Conf/Menu.php";
            $tree = require_once($file);
        }
        $menuModel = D('Menu');
        foreach ($tree as $value) {
            $add_pid = $menuModel->add(
                array(
                    'title'=>$value['title'],
                    'url'=>$value['url'],
                    'pid'=>$pid,
                    'hide'=>isset($value['hide'])? (int)$value['hide'] : 0,
                    'tip'=>isset($value['tip'])? $value['tip'] : '',
                    'group'=>$value['group'],
                )
            );
            if($value['operator']){
                $this->import($value['operator'], $add_pid);
            }
        }
    }

    public function import(){
        if(IS_POST){
            $tree = I('post.tree');
            $lists = explode(PHP_EOL, $tree);
            $menuModel = M('Menu');
            if($lists == array()){
                $this->error(lang('_PLEASE_FILL_IN_THE_FORM_OF_A_BATCH_IMPORT_MENU,_AT_LEAST_ONE_MENU_'));
            }else{
                $pid = I('post.pid');
                foreach ($lists as $key => $value) {
                    $record = explode('|', $value);
                    if(count($record) == 2){
                        $menuModel->add(array(
                            'title'=>$record[0],
                            'url'=>$record[1],
                            'pid'=>$pid,
                            'sort'=>0,
                            'hide'=>0,
                            'tip'=>'',
                            'is_dev'=>0,
                            'group'=>'',
                        ));
                    }
                }
                $this->success(lang('_IMPORT_SUCCESS_'),U('index?pid='.$pid));
            }
        }else{
            $this->meta_title = lang('_BATCH_IMPORT_BACKGROUND_MENU_');
            $pid = (int)I('get.pid');
            $this->assign('pid', $pid);
            $data = M('Menu')->where("id={$pid}")->field(true)->find();
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * 菜单排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort(){
        if(IS_GET){
            $ids = I('get.ids');
            $pid = I('get.pid');

            //获取排序的数据
            $map = array('status'=>array('gt',-1));
            $map['hide']=0;
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }else{
                if($pid !== ''){
                    $map['pid'] = $pid;
                }
            }
            $list = M('Menu')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = lang('_MENU_SORT_');
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value){
                $res = M('Menu')->where(array('id'=>$value))->setField('sort', $key+1);
            }
            if($res !== false){
                $this->success(lang('_SORT_OF_SUCCESS_'));
            }else{
                $this->eorror(lang('_SORT_OF_FAILURE_'));
            }
        }else{
            $this->error(lang('_ILLEGAL_REQUEST_'));
        }
    }
}
