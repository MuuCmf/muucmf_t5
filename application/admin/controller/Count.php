<?php
namespace app\admin\controller;

use app\admin\controller\Admin;
use think\Db;

class Count extends Admin{

    protected $countModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->assign('now_table',request()->action());
        $this->countModel=model('Count');
    }

    /**
     * 网站统计
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function index()
    {
        if(request()->isPost()){
            $count_day=input('post.count_day', C('COUNT_DAY'),'intval',7);
            if(M('Config')->where(array('name'=>'COUNT_DAY'))->setField('value',$count_day)===false){
                $this->error("设置失败！");
            }else{
                cache('DB_CONFIG_DATA',null);
                $this->success("设置成功！",'refresh');
            }

        }else{
            $this->meta_title = lang('_INDEX_MANAGE_');
            $today = date('Y-m-d', time());
            $today = strtotime($today);
            $count_day = config('COUNT_DAY',null,7);
            $count['count_day']=$count_day;
            for ($i = $count_day; $i--; $i >= 0) {
                $day = $today - $i * 86400;
                $day_after = $today - ($i - 1) * 86400;
                $week_map=array('Mon'=>lang('_MON_'),'Tue'=>lang('_TUES_'),'Wed'=>lang('_WEDNES_'),'Thu'=>lang('_THURS_'),'Fri'=>lang('_FRI_'),'Sat'=>'<strong>'.lang('_SATUR_').'</strong>','Sun'=>'<strong>'.lang('_SUN_').'</strong>');
                $week[] = date('m月d日 ', $day). $week_map[date('D',$day)];
                $user = UCenterMember()->where('status=1 and reg_time >=' . $day . ' and reg_time < ' . $day_after)->count() * 1;
                $registeredMemeberCount[] = $user;
                if ($i == 0) {
                    $count['today_user'] = $user;
                }
            }
            $week = json_encode($week);
            $this->assign('week', $week);
            $count['total_user'] = $userCount = UCenterMember()->where(array('status' => 1))->count();
            $count['today_action_log'] = M('ActionLog')->where('status=1 and create_time>=' . $today)->count();
            $count['last_day']['days'] = $week;
            $count['last_day']['data'] = json_encode($registeredMemeberCount);
            // dump($count);exit;
            if(C('SESSION_TYPE')=='db') {
                $count['now_inline'] = M('Session')->where(array('session_expire'=>array('gt',time())))->count() * 1;
            }

            $this->assign('count', $count);
            $this->meta_title = '网站统计';
            $this->display();
        }
    }

    /**
     * 流失率统计
     */
    public function lost($r=10)
    {
        if(request()->isPost()){
            $aLostLong=input('post.lost_long',30,'intval');
            if($aLostLong>=1){
                if(Db::name('Config')->where(array('name'=>'LOST_LONG'))->setField('value',$aLostLong)===false){
                    $this->error("设置失败！");
                }else{
                    cache('DB_CONFIG_DATA',null);
                    $this->success("设置成功！");
                }
            }
        }else{
            $day=config('LOST_LONG',null,30);
            $this->assign('lost_long',$day);
            $lostList=$this->countModel->getLostListPage([]);

            $page = $lostList->render();
            
            foreach($lostList as &$val){
                $val['date']=time_format($val['date'],'Y-m-d');
                $val['rate']=($val['rate']*100)."%";
            }
            unset($val);
            $this->assign('lostList',$lostList);
            $this->assign('page', $page);
            $this->setTitle('流失率统计');
            return $this->fetch();
        }
    }

    /**
     * 留存率统计
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function remain()
    {
        if(request()->isPost()){
            $aStartTime=input('post.startDate','','text');
            $aEndTime=input('post.endDate','','text');
            if($aStartTime==''||$aEndTime==''){
                $this->error('请选择时间段!');
            }
            $startTime=strtotime($aStartTime);
            $endTime=strtotime($aEndTime);
            $remainList=$this->countModel->getRemainList($startTime,$endTime);
            $this->assign('remainList',$remainList);
            $html=$this->fetch('Count/_remain_data');
            $this->show($html);
        }else{
            $today=date('Y-m-d 00:00',time());
            $startTime=strtotime($today." - 9 day");
            $endTime=strtotime($today." - 2 day");
            $remainList=$this->countModel->getRemainList($startTime,$endTime);
            $options=array('startDate'=>time_format(strtotime($today." - 9 day"),"Y-m-d"),'endDate'=>time_format(strtotime($today." - 2 day"),"Y-m-d"));
            $this->assign('options',$options);
            $this->assign('remainList',$remainList);
            $this->setTitle('留存率统计');
            return $this->fetch();
        }
    }

    /**
     * 活跃用户统计
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function active()
    {
        if(request()->isPost()){
            $aType=input('post.type','day','text');
            $aStartTime=input('post.startDate','','text');
            $aEndTime=input('post.endDate','','text');
            if($aStartTime==''||$aEndTime==''){
                $this->error('请选择时间段!');
            }
            $startTime=strtotime($aStartTime);
            $endTime=strtotime($aEndTime);
            if(!in_array($aType,array('week','month','day'))){
                $aType='day';
            }
            $activeList=$this->countModel->getActiveList($startTime,$endTime,$aType);
            $activeList['status']=1;
            $this->ajaxReturn($activeList);
        }else{
            $aType=input('get.type','day','text');
            switch($aType){
                case 'week':
                    $startTime=strtotime(date('Y-m-d').' - '.date('w').' day - 91 day');
                    break;
                case 'month':
                    $startTime=strtotime(date('Y-m-01').' - 9 month');
                    break;
                case 'day':
                default:
                    $aType='day';
                    $startTime=strtotime(date('Y-m-d').' - 9 day');
            }
            $this->assign('type',$aType);
            $options=array('startDate'=>time_format($startTime,"Y-m-d"),'endDate'=>time_format(time(),"Y-m-d"));
            $this->assign('options',$options);
            $activeList=$this->countModel->getActiveList($startTime,time(),$aType);
            $this->assign('activeList',json_encode($activeList));
            //dump($activeList);exit;
            $this->setTitle('活跃用户统计');
            return $this->fetch();
        }
    }

    /**
     * 设置活跃度绑定的行为
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function setActiveAction()
    {
        if(request()->isPost()){
            $aActiveAction=input('post.active_action',3,'intval');
            if(Db::name('Config')->where(['name'=>'COUNT_ACTIVE_ACTION'])->setField('value',$aActiveAction)===false){
                $this->error("设置失败！");
            }else{
                cache('DB_CONFIG_DATA',null);
                $this->success("设置成功！");
            }
        }else{
            $map['status']=1;
            $actionList=model('Action')->getAction($map);
            $this->assign('action_list',$actionList);
            $nowAction=config('COUNT_ACTIVE_ACTION',null,3);
            $this->assign('now_active_action',$nowAction);
            $this->meta_title = '设置活跃度绑定的行为';
            return $this->fetch('set_active_action');
        }
    }

    /**
     * 在线用户列表
     * @author:zzl(郑钟良) zzl@ourstu.com
     */
    public function nowUserList($r=20)
    {
        if(config('SESSION_TYPE')!='db'){
            $this->error('当前只支持session存入数据库的情况下进行在线用户列表统计！');
        }
        $sessionModel=Db::name('Session');
        $map['session_expire']=array('gt',time());
        $totalCount=$sessionModel->where($map)->count()*1;
        $map['session_data']=array('neq','');
        $loginCount=$sessionModel->where($map)->count()*1;
        $userList=$sessionModel->where($map)->page($page,$r)->field('session_id,session_expire')->select();
        $memberModel=Db::name('Member');
        foreach ($userList as &$val){
            $user=$memberModel->where(array('session_id'=>$val['session_id']))->find();
            if(!$user){
                $val['uid']=0;
                $val['nickname']='不是在网站端登录，没有对应上session_id';
                $val['last_login_time']=$user['last_login_time'];
                $val['id']=$val['session_id'];
                continue;
            }
            $val['uid']=$user['uid'];
            $val['nickname']=$user['nickname'];
            $val['last_login_time']=$user['last_login_time'];
            $val['id']=$val['session_id'];
        }
        unset($key,$val);

        $data['userList']=$userList;
        $data['loginCount']=$loginCount;
        $data['totalCount']=$totalCount;
        $this->assign($data);

        //生成翻页HTML代码
        C('VAR_PAGE', 'page');
        $pager = new \Think\Page($loginCount,$r, $_REQUEST);
        $pager->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %HEADER%');
        $paginationHtml = $pager->show();

        $this->assign('pagination', $paginationHtml);
        $this->meta_title = '流失率统计';
        $this->assign('now_table','now_user');
        $this->display('user');
    }

    /**
     * 下线在线用户
     * @param $ids
     * @author:zzl(郑钟良) zzl@ourstu.com
     */
    public function downUser($ids=0,$all=0)
    {
        !is_array($ids)&&$ids=explode(',',$ids);
        $sessionModel=M('Session');
        $memberModel=M('Member');
        $userTokenModel=M('UserToken');
        if($all){
            $map['session_data']=array('neq','');
            $sessionModel->where($map)->setField('session_data','');
            $userTokenModel->where(1)->delete();
        }else{
            $map['session_id']=array('in',$ids);
            $sessionModel->where($map)->setField('session_data','');
            $uids=$memberModel->where($map)->field('uid')->select();
            $uids=array_column($uids,'uid');
            $userTokenModel->where(array('uid'=>array('in',$uids)))->delete();
        }
        $this->success('操作成功！');
    }
} 