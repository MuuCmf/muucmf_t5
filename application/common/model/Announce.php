<?php
namespace app\common\model;


use think\Model;

class Announce extends Model{

    public function getListPage($map,$page=1,$order='create_time desc',$r=10)
    {
        $totalCount=$this->where($map)->count();
        if($totalCount){
            $list=$this->where($map)->order($order)->page($page,$r)->select();
        }
        return array($list,$totalCount);
    }

    public function addData($data)
    {
        $data=$this->create($data);
        $res=$this->add($data);
        return $res;
    }

    public function saveData($data)
    {
        $data=$this->create($data);
        $res=$this->save($data);
        S('Announce_detail_'.$data['id'],null);
        return $res;
    }

    public function getById($id)
    {
        $data=S('Announce_detail_'.$id);
        if($data===false){
            $data=$this->find($id);
            S('Announce_detail_'.$id,$data);
        }
        return $data;
    }


} 