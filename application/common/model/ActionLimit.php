<?php
namespace app\common\model;

use think\Model;
use think\Db;

class ActionLimit extends Model
{

    var $item = [];
    var $state = true;
    var $url;
    var $info = '';
    var $punish = array(
        array('warning','警告并禁止'),
        array('logout_account', '强制退出登陆'),
        array('ban_account', '封停账户'),
        array('ban_ip', '封IP'),
    );

    public function _initialize()
    {
        $this->url = '';
        $this->info = '';
        $this->state = true;

        parent::_initialize();
    }
    protected $autoWriteTimestamp = true;
    

    /**
     * ban_account  封停帐号
     * @param $item
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function ban_account($item)
    {
        set_user_status($item['user_id'], 0);
    }

    public function ban_ip($item,$val)
    {
       //TODO 进行封停IP的操作
    }

    public function warning($item,$val){
        $this->state = false;
        $this->info = lang('_OPERATION_IS_FREQUENT_PLEASE_').$val['time_number'].get_time_unit($val['time_unit']).lang('_AND_THEN_');
        $this->url = Url('index/index/index');
    }

    public function getList($where){
        $list = collection($this->where($where)->select())->toArray();
        return $list;
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
        $item['action_ip'] = $item['action_ip'] ? request()->ip(1) : null;
        foreach ($item as $k => $v) {
            if (empty($v)) {
                unset($item[$k]);
            }
        }
        unset($k, $v);

        $limitList = $this->where('action_list','like','%'.$item['action'].'%')->where('status','=',1)->select();
        $item['action_id'] = Db::name('action')->where(['name' => $item['action']])->field('id')->find();
        $item['action_id'] = implode($item['action_id']);
        unset($item['action']);

        foreach ($limitList as &$val) {
            $ago = get_time_ago($val['time_unit'], $val['time_number'], time());

            $item['create_time'] = ['egt', $ago];

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

    /**
     * [editData description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editData($data)
    {
        if($data['id']){
            $res = $this->allowField(true)->save($data,$data['id']);
        }else{
            $res = $this->allowField(true)->save($data);
        }
        
        return $res;
    }

    /**
     * Gets the list by page.
     *
     * @param      <type>   $map    The map
     * @param      string   $order  The order
     * @param      string   $field  The field
     * @param      integer  $r      { parameter_description }
     *
     * @return     <type>   The list by page.
     */
    public function getListByPage($map,$order='create_time desc',$field='*',$r=20)
    {
        $list = $this->where($map)->order($order)->field($field)->paginate($r,false,['query'=>request()->param()]);

        return $list;
    }


}















