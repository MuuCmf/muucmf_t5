<?php
namespace app\admin\Controller;

use think\Db;
/**
 * 后台频道控制器
 */

class Channel extends Admin
{

    /**
     * 频道列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index()
    {
        $Channel = Db::name('Channel');
        if (request()->isPost()) {
            $one = $_POST['nav'][1];
            if (count($one) > 0) {
                Db::execute('TRUNCATE TABLE ' . config('database.prefix') . 'channel');

                for ($i = 0; $i < count(reset($one)); $i++) {
                    $data[$i] = array(
                        'pid' => 0,
                        'title' => html($one['title'][$i]),
                        'url' => text($one['url'][$i]),
                        'sort' => intval($one['sort'][$i]),
                        'target' => intval($one['target'][$i]),
                        'band_text' => text($one['band_text'][$i]),
                        'band_color' => text($one['band_color'][$i]),
                        'status' => 1
                    );
                    $pid[$i] = $Channel->insert($data[$i]);
                }
                $two = $_POST['nav'][2];

                for ($j = 0; $j < count(reset($two)); $j++) {
                    $data_two[$j] = array(
                        'pid' => $pid[$two['pid'][$j]],
                        'title' => html($two['title'][$j]),
                        'url' => text($two['url'][$j]),
                        'sort' => intval($two['sort'][$j]),
                        'target' => intval($two['target'][$j]),
                        'band_text' => text($two['band_text'][$j]),
                        'band_color' => text($two['band_color'][$j]),
                        'status' => 1
                    );
                    $res[$j] = $Channel->insert($data_two[$j]);
                }
                cache('common_nav',null);
                $this->success(lang('_CHANGE_'));
            }
            $this->error(lang('_NAVIGATION_AT_LEAST_ONE_'));


        } else {
            /* 获取频道列表 */
            $map = array('status' => array('gt', -1), 'pid' => 0);
            $list = $Channel->where($map)->order('sort asc,id asc')->select();
            foreach ($list as $k => &$v) {
                $module = Db::name('Module')->where(array('entry' => $v['url']))->find();
                $v['module_name'] = $module['name'];
                $child = $Channel->where(array('status' => array('gt', -1), 'pid' => $v['id']))->order('sort asc,id asc')->select();
                foreach ($child as $key => &$val) {
                    $module = Db::name('Module')->where(array('entry' => $val['url']))->find();
                    $val['module_name'] = $module['name'];
                }
                unset($key, $val);
                $child && $v['child'] = $child;
            }
            unset($k, $v);

            $this->assign('module', $this->getModules());
            $this->assign('list', $list);

            $this->setTitle(lang('_NAVIGATION_MANAGEMENT_'));
            return $this->fetch();
        }

    }


    public function user(){
        $Channel = Db::name('UserNav');
        if (request()->isPost()) {
            $one = $_POST['nav'][1];
            if (count($one) > 0) {
                Db::execute('TRUNCATE TABLE ' . config('database.prefix') . 'user_nav');

                for ($i = 0; $i < count(reset($one)); $i++) {
                    $data[$i] = array(
                        'title' => text($one['title'][$i]),
                        'url' => text($one['url'][$i]),
                        'sort' => intval($one['sort'][$i]),
                        'target' => intval($one['target'][$i]),
                        'color' => text($one['color'][$i]),
                        'band_text' => text($one['band_text'][$i]),
                        'band_color' => text($one['band_color'][$i]),
                        'icon' => text(str_replace('icon-', '', $one['icon'][$i])),
                        'status' => 1

                    );
                    $pid[$i] = $Channel->insert($data[$i]);
                }
                cache('common_user_nav',null);
                $this->success(lang('_CHANGE_'));
            }
            $this->error(lang('_NAVIGATION_AT_LEAST_ONE_'));


        } else {
            /* 获取频道列表 */
            $map = array('status' => array('gt', -1));
            $list = $Channel->where($map)->order('sort asc,id asc')->select();
            foreach ($list as $k => &$v) {
                $module = Db::name('Module')->where(array('entry' => $v['url']))->find();
                $v['module_name'] = $module['name'];
                unset($key, $val);
            }

            unset($k, $v);
            $this->assign('module', $this->getModules());
            $this->assign('list', $list);

            $this->setTitle(lang('_NAVIGATION_MANAGEMENT_'));
            return $this->fetch();
        }
    }
    public function getModule()
    {
        $this->success($this->getModules());
    }


    /**
     * 频道列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index1()
    {
        $pid = input('get.pid', 0);
        /* 获取频道列表 */
        $map = array('status' => array('gt', -1), 'pid' => $pid);
        $list = M('Channel')->where($map)->order('sort asc,id asc')->select();

        $this->assign('list', $list);
        $this->assign('pid', $pid);
        $this->meta_title = lang('_NAVIGATION_MANAGEMENT_');
        $this->display();
    }


    /**
     * 添加频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function add()
    {
        if (request()->isPost()) {
            $Channel = Db::name('Channel');
            $data = input('');
            if ($data) {
                $id = $Channel->insert();
                if ($id) {
                    $this->success(lang('_NEW_SUCCESS_'), U('index'));
                    //记录行为
                    action_log('update_channel', 'channel', $id, UID);
                } else {
                    $this->error(lang('_NEW_FAILURE_'));
                }
            } else {
                $this->error($Channel->getError());
            }
        } else {
            $pid = input('get.pid', 0);
            //获取父导航
            if (!empty($pid)) {
                $parent = Db::name('Channel')->where(array('id' => $pid))->field('title')->find();
                $this->assign('parent', $parent);
            }
            $pnav = Db::name('Channel')->where(array('pid' => 0))->select();
            $this->assign('pnav', $pnav);
            $this->assign('pid', $pid);
            $this->assign('info', null);
            $this->setTitle(lang('_NEW_NAVIGATION_'));
            return $this->fetch('edit');
        }
    }

    /**
     * 编辑频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function edit($id = 0)
    {
        if (IS_POST) {
            $Channel = D('Channel');
            $data = $Channel->create();
            if ($data) {
                if ($Channel->save()) {
                    //记录行为
                    action_log('update_channel', 'channel', $data['id'], UID);
                    $this->success(lang('_SUCCESS_EDIT_'), U('index'));
                } else {
                    $this->error(lang('_EDIT_FAILED_'));
                }

            } else {
                $this->error($Channel->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Channel')->find($id);

            if (false === $info) {
                $this->error(lang('_GET_CONFIGURATION_INFORMATION_ERROR_'));
            }

            $pid = i('get.pid', 0);

            //获取父导航
            if (!empty($pid)) {
                $parent = M('Channel')->where(array('id' => $pid))->field('title')->find();
                $this->assign('parent', $parent);
            }
            $pnav = D('Channel')->where(array('pid' => 0))->select();
            $this->assign('pnav', $pnav);
            $this->assign('pid', $pid);
            $this->assign('info', $info);
            $this->meta_title = lang('_EDIT_NAVIGATION_');
            $this->display();
        }
    }

    /**
     * 删除频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function del()
    {
        $id = array_unique((array)I('id', 0));

        if (empty($id)) {
            $this->error(lang('_PLEASE_CHOOSE_TO_OPERATE_THE_DATA_'));
        }

        $map = array('id' => array('in', $id));
        if (M('Channel')->where($map)->delete()) {
            //记录行为
            action_log('update_channel', 'channel', $id, UID);
            $this->success(lang('_DELETE_SUCCESS_'));
        } else {
            $this->error(lang('_DELETE_FAILED_'));
        }
    }

    /**
     * 导航排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort()
    {
        if (IS_GET) {
            $ids = I('get.ids');
            $pid = I('get.pid');

            //获取排序的数据
            $map = array('status' => array('gt', -1));
            if (!empty($ids)) {
                $map['id'] = array('in', $ids);
            } else {
                if ($pid !== '') {
                    $map['pid'] = $pid;
                }
            }
            $list = M('Channel')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = lang('_NAVIGATION_SORT_');
            $this->display();
        } elseif (IS_POST) {
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key => $value) {
                $res = M('Channel')->where(array('id' => $value))->setField('sort', $key + 1);
            }
            if ($res !== false) {
                $this->success(lang('_SORT_OF_SUCCESS_'));
            } else {
                $this->eorror(lang('_SORT_OF_FAILURE_'));
            }
        } else {
            $this->error(lang('_ILLEGAL_REQUEST_'));
        }
    }
}
