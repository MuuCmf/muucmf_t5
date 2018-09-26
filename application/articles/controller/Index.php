<?php
namespace app\articles\controller;

use app\common\controller\Common;
use think\Db;

class Index extends Common
{
    public function _initialize()
    {
        parent::_initialize();

        $tree = model('ArticlesCategory')->getTree(0,true,['status' => 1]);

        foreach ($tree as $v) {
            $m = [
                'tab' => 'category_' . $v['id'], 
                'title' => $v['title'], 
                'href' => Url('Articles/index/category', ['id' => $v['id']])
            ];
            if (isset($v['_'])) {
                $m['children'][] = [
                    'title' => '全部', 
                    'href' => Url('Articles/index/category', ['id' => $v['id']])
                ];
                foreach ($v['_'] as $child){
                    $m['children'][] = [
                        'title' => $child['title'], 
                        'href' => Url('Articles/index/category', ['id' => $child['id']])
                    ];
                }     
            }else{
                $m['children'] = '';
            }
            $c_menu[]=$m;
        }
        
        $this->assign('sub_menu', $c_menu);
        
    }
    /**
     * 文章首页
     * @return [type] [description]
     */
    public function index()
    {
        // 文章首页
        $map['status']=1;
        /* 获取当前分类下文章列表 */
        $articles = model('Articles');
        // 查询数据集
        $list = $articles->where($map)->order('id', 'desc')->paginate(2);
        foreach($list as &$val){
            $val['user']=query_user(['space_url','avatar32','nickname'],$val['uid']);
        }
        unset($val);

        /* 模板赋值并渲染模板 */
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function category($r=20)
    {
        /* 分类信息 */
        $cid = input('cid',0,'intval');
        if($cid){
            //$categoryT = $this->_category($$cid);
            $cates=model('ArticlesCategory')->getCategoryList(['pid'=>$cid]);
            if(count($cates)){
                $cates=array_column($cates,'id');
                $cates=array_merge(array($cid),$cates);
                $map['category']=array('in',$cates);
            }else{
                $map['category']=$$cid;
            }
        }
        $map['status']=1;
        /* 获取当前分类下文章列表 */
        $list = model('Articles')->where($map)->order('id', 'desc')->paginate($r);
        foreach($list as &$val){
            $val['user']=query_user(['space_url','avatar32','nickname'],$val['uid']);
        }
        unset($val);
        //dump($list);exit;
        /* 模板赋值并渲染模板 */
        $this->assign('list', $list);
        $this->assign('cid', $cid);

        return $this->fetch();
    }

    
}
