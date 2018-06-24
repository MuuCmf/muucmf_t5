<?php
namespace app\admin\model;

use think\Model;

class RoleConfig extends Model
{

    public function addData($data){
        
        if(!$data) return false;
        $data['update_time']=time();
        $result=$this->save($data);
        return $result;
    }

    public function saveData($map=array(),$data=array()){
        $data['update_time']=time();
        $result=$this->where($map)->save($data);
        return $result;
    }

    public function deleteData($map){
        $result=$this->where($map)->delete();
        return $result;
    }
} 