<?php

namespace Articles\Widget;
use Think\Controller;

/**
 * 分类widget
 * 用于动态调用分类信息
 */

class PositionWidget extends Controller{
	
	/* 显示指定分类的同级分类或子分类列表 */
	public function lists($position=1,$category,$limit=5,$filed){
        /**
         * 获取推荐位数据列表
         * @param  number  $pos      推荐位 1:幻灯展示2:首页推荐4:栏目推荐8:内页推荐
         * @param  number  $category 分类ID
         * @param  number  $limit    列表行数
         * @param  boolean $filed    查询字段
         * @return array             数据列表
         */
		$lists=D('Articles/Articles')->position($position,$category,$limit,$filed);
        foreach($lists as &$val){
            $val['user']=query_user(array('space_url','nickname'),$val['uid']);
        }
		$this->assign('lists', $lists);
		$this->display('Widget/position');
	}
	
}
