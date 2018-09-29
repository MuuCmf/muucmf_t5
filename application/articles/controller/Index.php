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
        /*用户操作菜单*/
        $show_edit=cache('SHOW_EDIT_BUTTON');
        if($show_edit===false){
            $map['can_post']=1;
            $map['status']=1;
            $show_edit=model('ArticlesCategory')->where($map)->count();
            cache('SHOW_EDIT_BUTTON',$show_edit);
        }
        $action_menu=[];
        if(is_login()){
            $action_menu[]=['tab' => 'myArticles', 'title' => '<i class="icon-th-list"></i> 我发布的', 'href' =>Url('articles/User/my')];
            if($show_edit){
            $action_menu[]=['tab' => 'create', 'title' => '<i class="icon-edit"></i> 发布文章', 'href' =>is_login()?Url('articles/User/edit'):"javascript:toast.error('登录后才能操作')"];
            }
        }
        
        $this->assign('sub_menu', $c_menu);
        $this->assign('action_menu', $action_menu);
        
    }
    /**
     * 文章首页
     * @return [type] [description]
     */
    public function index($r=20)
    {
        // 文章首页
        $map['status']=1;
        // 查询数据集
        $list = model('Articles')->where($map)->order('id', 'desc')->paginate($r);
        foreach($list as &$val){
            $val['user']=query_user(['space_url','avatar32','nickname'],$val['uid']);
        }
        unset($val);

        /* 模板赋值并渲染模板 */
        $this->assign('cid', 0);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function category($r=20)
    {
        /* 分类信息 */
        $cid = input('id',0,'intval');
        if($cid){
            //$categoryT = $this->_category($$cid);
            $cates=model('ArticlesCategory')->getCategoryList(['pid'=>$cid]);
            if(count($cates)){
                $cates=array_column($cates,'id');
                $cates=array_merge(array($cid),$cates);
                $map['category']=array('in',$cates);
            }else{
                $map['category']=$cid;
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

    public function detail()
    {
        $aId=input('id',0,'intval');

        /* 标识正确性检测 */
        if (!($aId && is_numeric($aId))) {
            $this->error('文档ID错误！');
        }

        $info=model('Articles')->getDataById($aId);
        
        $author=query_user(['uid','space_url','nickname','avatar32','avatar64','signature'],$info['uid']);
        $author['articles_count']=model('Articles')->where(['uid'=>$info['uid']])->count();
        //关键字转化成数组
        $keywords = explode(',',$info['keywords']);

        /*用户所要文章访问量*/
        $author['articles_view']=$this->_totalView($info['uid']);
        $this->_category($info['category']);

        /* 更新浏览数 */
        $map = ['id' => $aId];
        model('Articles')->where($map)->setInc('view');
        
        /* 模板赋值并渲染模板 */
        $this->assign('author',$author);
        $this->assign('info', $info);
        
        //dump($info);exit;
        return $this->fetch();
    }
    /**
     * 作者文章列表
     * @return [type] [description]
     */
    public function author($r=20)
    {
        $uid = input('uid',0,'intval');
        $map['uid'] = $uid;
        $map['status']=1;
        // 查询数据集
        $list = model('Articles')->where($map)->order('id', 'desc')->paginate($r);
        foreach($list as &$val){
            $val['user']=query_user(['space_url','avatar32','nickname'],$val['uid']);
        }
        unset($val);
        /*作者信息*/
        $author=query_user(['uid','space_url','nickname','avatar32','avatar64','signature'],$uid);
        $author['articles_count']=model('Articles')->where(['uid'=>$uid])->count();
        /*用户所要文章访问量*/
        $author['articles_view']=$this->_totalView($uid);
        /* 模板赋值并渲染模板 */
        $this->assign('uid', $uid);
        $this->assign('author',$author);
        $this->assign('list', $list);
        return $this->fetch();
    }

    //获取用户文章数的总阅读量
    private function _totalView($uid=0)
    {
        $total = cache("article_total_view_uid_{$uid}");
        if(!$total){
            $res=model('Articles')->where(['uid'=>$uid])->select();
            $total=0;
            foreach($res as $value){ 
                $total=$total+$value['view'];
            }
            unset($value);
            cache("article_total_view_uid_{$uid}",$total,3600);
        }
        return $total;
    }

    private function _category($id=0)
    {
        $now_category=model('ArticlesCategory')->getTree($id,'id,title,pid,sort');
        $this->assign('now_category',$now_category);
        return $now_category;
    }

    
}
