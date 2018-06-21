<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-13
 * Time: PM4:54
 */

namespace Admin\Builder;

class AdminSortBuilder extends AdminBuilder {
    private $_title;
    private $_list;
    private $_buttonList;
    private $_savePostUrl;

    public function title($title) {
        $this->title = $title;
        $this->meta_title=$title;
        return $this;
    }

    public function data($list) {
        $this->_list = $list;
        return $this;
    }

    public function button($title, $attr=array()) {
        $this->_buttonList[] = array('title'=>$title, 'attr'=>$attr);
        return $this;
    }

    public function buttonSubmit($url, $title='确定') {
        $this->savePostUrl($url);

        $attr = array();
        $attr['class'] = "sort_confirm btn submit-btn";
        $attr['type'] = 'button';
        $attr['target-form'] = 'form-horizontal';
        return $this->button($title, $attr);
    }

    public function buttonBack($url=null, $title='返回') {
        //默认返回当前页面
        if(!$url) {
            $url = $_SERVER['HTTP_REFERER'];
        }

        //添加按钮
        $attr = array();
        $attr['href'] = $url;
        $attr['onclick'] = 'javascript: location.href=$(this).attr("href");';
        $attr['class'] = 'sort_cancel btn btn-return';
        return $this->button($title, $attr);
    }

    public function savePostUrl($url) {
        $this->_savePostUrl = $url;
    }

    public function display() {
        //编译按钮的属性
        foreach($this->_buttonList as &$e) {
            $e['attr'] = $this->compileHtmlAttr($e['attr']);
        }
        unset($e);

        //显示页面
        $this->assign('title', $this->_title);
        $this->assign('list', $this->_list);
        $this->assign('buttonList', $this->_buttonList);
        $this->assign('savePostUrl', $this->_savePostUrl);
        parent::display('admin_sort');
    }

    public function doSort($table, $ids) {
        $ids = explode(',', $ids);
        $res = 0;
        foreach ($ids as $key=>$value){
            $res += M($table)->where(array('id'=>$value))->setField('sort', $key+1);
        }
        if(!$res) {
            $this->error(L('_ERROR_SORT_').L('_PERIOD_'));
        } else {
            $this->success(L('_SUCCESS_SORT_').L('_PERIOD_'), cookie('__SELF__'));
        }
    }
}