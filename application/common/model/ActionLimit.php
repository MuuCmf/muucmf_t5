<?php
namespace app\common\model;

use think\Model;
use think\Db;

class ActionLimit extends Model
{
    var $state = true;

    function __construct()
    {
        $this->url = '';
        $this->info = '';
        $this->state = true;
    }
    protected $autoWriteTimestamp = true;
    
    public function addActionLimit($data)
    {
        $res = $this->add($data);
        return $res;
    }

    public function getActionLimit($where){
        $limit = $this->where($where)->find();
        return $limit;
    }

    public function getList($where){
        $list = collection($this->where($where)->select())->toArray();
        return $list;
    }


    public function editActionLimit($data)
    {
        $res = $this->save($data);
        return $res;
    }

    public function addCheckItem($action = null, $model = null, $record_id = null, $user_id = null, $ip = false)
    {
        $this->item[] = array('action' => $action, 'model' => $model, 'record_id' => $record_id, 'user_id' => $user_id, 'action_ip' => $ip);
        return $this;
    }


    public function check()
    {
        $items = $this->item;
        foreach ($items as &$item) {
            $this->checkOne($item);
        }
        unset($item);
    }

    public function checkOne($item)
    {
        $item['action_ip'] = $item['action_ip'] ? get_client_ip(1) : null;
        foreach ($item as $k => $v) {
            if (empty($v)) {
                unset($item[$k]);
            }
        }
        unset($k, $v);
        $time = time();
        $map[] = ['action_list','like',$item['action']];
        $map[] = ['status','=',1];
        $limitList = Db::name('actionLimit')->where($map)->select();
        dump('sdfsdf');exit;
        $item['action_id'] = Db::name('action')->where(array('name' => $item['action']))->field('id')->find();

        $item['action_id'] = implode($item['action_id']);
        unset($item['action']);
        foreach ($limitList as &$val) {
            $ago = get_time_ago($val['time_unit'], $val['time_number'], $time);

            $item['create_time'] = array('egt', $ago);

            $log = Db::name('actionLog')->where($item)->order('create_time desc')->select();
            
            if (count($log) >= $val['frequency']) {
                $punishes = explode(',', $val['punish']);
                foreach ($punishes as $punish) {
                    //执行惩罚
                    if (method_exists($this, $punish)) {
                        $this->$punish($item,$val);
                    }
                }
                unset($punish);
                if ($val['if_message']) {
                    model('Message')->sendMessageWithoutCheckSelf($item['user_id'], lang('_SYSTEM_MESSAGE_'),$val['message_content'],$_SERVER['HTTP_REFERER']);
                }
            }
        }
        unset($val);
    }


}















