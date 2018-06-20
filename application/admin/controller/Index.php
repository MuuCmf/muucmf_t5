<?php
namespace app\admin\Controller;

use app\admin\controller\Admin;
use think\Db;

class Index extends Admin
{
    /**
     * 后台首页
     */
    public function index()
    {

        return $this->fetch();
 
    }
    /**
     * 控制台首页
     * @return [type] [description]
     */
    public function console()
    {
        
        if(request()->isPost()){
            $count_day=input('post.count_day', config('COUNT_DAY'),'intval',7);
            if(Db::name('Config')->where(array('name'=>'COUNT_DAY'))->setField('value',$count_day)===false){
                $this->error(lang('_ERROR_SETTING_').lang('_PERIOD_'));
            }else{
               cache('DB_CONFIG_DATA',null);
                $this->success(lang('_SUCCESS_SETTING_').lang('_PERIOD_'),'refresh');
            }

        }else{
            
            $this->getUserCount();
            $this->meta_title = lang('_INDEX_MANAGE_');
            $this->assign('meta_title',$this->meta_title);
            $this->getOtherCount();
            return $this->fetch();
        }
        

    }
    private function getOtherCount(){
        $countModel=model('Count');
        list($lostList,$totalCount)=$countModel->getLostListPage($map=1,1,5);
        foreach($lostList as &$val){
            $val['date']=time_format($val['date'],'Y-m-d');
            $val['rate']=($val['rate']*100)."%";
        }
        unset($val);
        $this->assign('lostList',$lostList);

        $today=date('Y-m-d 00:00',time());
        $startTime=strtotime($today." - 10 day");
        $endTime=strtotime($today);

        $startTime=strtotime(date('Y-m-d').' - 9 day');
        $activeList=$countModel->getActiveList($startTime,time(),'day');
        $this->assign('activeList',json_encode($activeList));

        $startTime=strtotime(date('Y-m-d').' - '.date('w').' day - 49 day');
        $weekActiveList=$countModel->getActiveList($startTime,time(),'week');
        $this->assign('weekActiveList',json_encode($weekActiveList));

        $startTime=strtotime(date('Y-m-01').' - 9 month');
        $monthActiveList=$countModel->getActiveList($startTime,time(),'month');
        $this->assign('monthActiveList',json_encode($monthActiveList));

        $startTime=strtotime($today." - 9 day");
        $endTime=strtotime($today." - 2 day");
        $remainList=$countModel->getRemainList($startTime,$endTime);
        $this->assign('remainList',$remainList);
        return true;
    }

    private function getUserCount(){
        $today = date('Y-m-d', time());
        $today = strtotime($today);
        $count_day = config('count_day');
        $count['count_day'] = $count_day;
        $week = [];
        $registeredMemeberCount = [];
        $count['today_user'] = 0;
        for ($i = $count_day; $i--; $i >= 0) {
            $day = $today - $i * 86400;
            $day_after = $today - ($i - 1) * 86400;
            $week_map = array('Mon' => lang('_MON_'), 'Tue' => lang('_TUES_'), 'Wed' => lang('_WEDNES_'), 'Thu' => lang('_THURS_'), 'Fri' => lang('_FRI_'), 'Sat' => '<strong>' . lang('_SATUR_') . '</strong>', 'Sun' => '<strong>' . lang('_SUN_') . '</strong>');
            $week[] = date('m月d日 ', $day) . $week_map[date('D', $day)];
            $user = Db::name('UcenterMember')->where('status=1 and reg_time >=' . $day . ' and reg_time < ' . $day_after)->count() * 1;
            $registeredMemeberCount[] = $user;
            if ($i == 0) {
                $count['today_user'] = $user;
            }
        }
        $week = json_encode($week);
        $this->assign('week', $week);
        $count['total_user'] = $userCount = Db::name('UcenterMember')->where(array('status' => 1))->count();
        $count['today_action_log'] = Db::name('ActionLog')->where('status=1 and create_time>=' . $today)->count();
        $count['last_day']['days'] = $week;
        $count['last_day']['data'] = json_encode($registeredMemeberCount);
        $count['now_inline']=Db::name('Session')->where(1)->count()*1;
        $this->assign('count', $count);
        //dump($count);exit;
    }

    /**
     * 保存用户统计设置
     */
    private function saveUserCount()
    {
        $count_day = input('post.count_day', config('COUNT_DAY'), 'intval', 7);
        if (Db::name('config')->where(array('name' => 'COUNT_DAY'))->setField('value', $count_day) === false) {
            $this->error(lang('_ERROR_SETTING_') . lang('_PERIOD_'));
        } else {
            cache('DB_CONFIG_DATA', null);
            $this->success(lang('_SUCCESS_SETTING_'), 'refresh');
        }
    }
    

}
