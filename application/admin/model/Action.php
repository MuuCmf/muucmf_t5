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

        /* 获取数据对象 */
        $data = input('');

        if(empty($data)){
            return false;
        }

        $action_rule = $data['action_rule'];
        if(!empty($action_rule)){

            for($i=0;$i<count($action_rule['table']);$i++){
                $data['rule'][] = [
                    'table'=>$action_rule['table'][$i],
                    'field'=>$action_rule['field'][$i],
                    'rule'=>$action_rule['rule'][$i],
                    'cycle'=>$action_rule['cycle'][$i],
                    'max'=>$action_rule['max'][$i],
                ];
            }
        }
        
        if(empty($data['rule'])){
            $data['rule'] ='';
        }else{
            $data['rule'] = serialize($data['rule']);
        }
        
        unset($data['action_rule']);
        /* 添加或新增行为 */
        if(empty($data['id'])){ //新增数据
            
            $res = $this->save($data); //添加行为
            if(!$res){
                $this->error = lang('_NEW_BEHAVIOR_WITH_EXCLAMATION_');
                return false;
            }
        } else { //更新数据
            $res = $this->save($data,['id'=>$data['id']]); //更新基础内容
            if(!$res){
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
