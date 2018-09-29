<?php
namespace app\articles\model;
use think\Model;

class Articles extends Model {

	//自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    public function editData($data)
    {
    	if(!mb_strlen($data['description'],'utf-8')){
            $data['description'] = msubstr(text($data['content']),0,200);
        }
        $detail['content'] = $data['content'];
        $detail['template'] = $data['template'];

        if($data['id']){
        	$data['update_time'] = time();
            $res = $this->allowField(true)->save($data,$data['id']);
            $detail['articles_id'] = $data['id'];
        }else{
        	$data['create_time'] = $data['update_time'] = time();
            $res = $this->allowField(true)->save($data);
            $detail['articles_id'] = $this->id;
        }
        if($res){
            model('articles/ArticlesDetail')->editData($detail);
        }
        return $res;
    }

    public function getDataById($id)
    {
        if($id>0){
            $map['id']=$id;
            $data=$this->get($map);
            if($data){
                $data['detail']=model('articles/ArticlesDetail')->getDataById($id);
            }
            return $data;
        }
        return null;
    }

    public function getListByUid($uid,$limit=5,$order = 'create_time desc')
    {
    	$map['uid'] = $uid;
    	$list = $this->where($map)->limit($limit)->order($order)->select();
    	$category=model('ArticlesCategory')->_category();

        foreach($list as &$val){
            $val['category_title']=$category[$val['category']]['title'];
        }
        unset($val);
    	return $list;
    }
    

}