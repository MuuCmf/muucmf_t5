<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace app\admin\Model;
use think\Model;

/**
 * 行为模型
 */

class Action extends Model {

    /**
     * 新增或更新一个行为
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     * @author huajie <banhuajie@163.com>
     */
    public function updateAction(){

        $action_rule = $_POST['action_rule'];
        for($i=0;$i<count($action_rule['table']);$i++){
            $_POST['rule'][] = array('table'=>$action_rule['table'][$i],'field'=>$action_rule['field'][$i],'rule'=>$action_rule['rule'][$i],'cycle'=>$action_rule['cycle'][$i],'max'=>$action_rule['max'][$i],);
        }
        if(empty($_POST['rule'])){
            $_POST['rule'] ='';
        }else{
            $_POST['rule'] = serialize($_POST['rule']);
        }
        /* 获取数据对象 */
        $data = $_POST;
        if(empty($data)){
            return false;
        }

        /* 添加或新增行为 */
        if(empty($data['id'])){ //新增数据
            $id = $this->add(); //添加行为
            if(!$id){
                $this->error = lang('_NEW_BEHAVIOR_WITH_EXCLAMATION_');
                return false;
            }
        } else { //更新数据
            $status = $this->save(); //更新基础内容
            if(false === $status){
                $this->error = lang('_UPDATE_BEHAVIOR_WITH_EXCLAMATION_');
                return false;
            }
        }
        //删除缓存
        cache('action_list', null);

        //内容添加或更新完成
        return $data;

    }


    public function getAction($map){
        $result = collection($this->where($map)->select())->toArray();
        return $result;
    }

    public function getActionOpt(){
        $result = collection($this->where(['status'=>1])->field('name,title')->select())->toArray();
        return $result;
    }

}
