<?php
namespace app\articles\controller;

use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminTreeListBuilder;
use app\admin\controller\Admin as MuuAdmin;


class Admin extends MuuAdmin
{
    public function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 配置页面
     * @return [type] [description]
     */
    public function config()
    {
        $builder=new AdminConfigBuilder();
        $data=$builder->handleConfig();
        $default_position=<<<str
1:幻灯展示
2:首页推荐
4:栏目推荐
str;

        $builder
            ->title('文章基础设置')
            ->data($data);

        $builder
            ->keyTextArea('ARTICLES_SHOW_POSITION','展示位配置')
            ->keyDefault('ARTICLES_SHOW_POSITION',$default_position)
            ->keyText('ARTICLES_SHOW_TITLE', '标题名称', '在首页展示块的标题')->keyDefault('ARTICLES_SHOW_TITLE','热门资讯')
            ->keyText('ARTICLES_SHOW_DESCRIPTION', '简短描述', '精简的描述模块内容')->keyDefault('ARTICLES_SHOW_DESCRIPTION','模块简单描述')
            ->keyText('ARTICLES_SHOW_COUNT', '显示文章的个数', '只有在网站首页模块中启用了资讯块之后才会显示')->keyDefault('ARTICLES_SHOW_COUNT',4)
            ->keyRadio('ARTICLES_SHOW_TYPE', '资讯的筛选范围', '', array('1' => '后台推荐', '0' => '全部'))->keyDefault('ARTICLES_SHOW_TYPE',0)
            ->keyRadio('ARTICLES_SHOW_ORDER_FIELD', '排序值', '展示模块的数据排序方式', array('view' => '阅读数', 'create_time' => '发表时间', 'update_time' => '更新时间'))->keyDefault('ARTICLES_SHOW_ORDER_FIELD','view')
            ->keyRadio('ARTICLES_SHOW_ORDER_TYPE', '排序方式', '展示模块的数据排序方式', array('desc' => '倒序，从大到小', 'asc' => '正序，从小到大'))->keyDefault('ARTICLES_SHOW_ORDER_TYPE','desc')
            ->keyText('ARTICLES_SHOW_CACHE_TIME', '缓存时间', '默认600秒，以秒为单位')->keyDefault('ARTICLES_SHOW_CACHE_TIME','600')

            ->group('基本配置', 'ARTICLES_SHOW_POSITION')->group('首页展示配置', 'ARTICLES_SHOW_COUNT,ARTICLES_SHOW_TITLE,ARTICLES_SHOW_DESCRIPTION,ARTICLES_SHOW_TYPE,ARTICLES_SHOW_ORDER_TYPE,ARTICLES_SHOW_ORDER_FIELD,ARTICLES_SHOW_CACHE_TIME')
            ->groupLocalComment('本地评论配置','index')
            ->buttonSubmit()
            ->buttonBack()
            ->display();
    }
    /**
     * 栏目分类
     * @return [type] [description]
     */
    public function category()
    {
        //显示页面
        $builder = new AdminTreeListBuilder();

        $tree = model('ArticlesCategory')->getTree(0, 'id,title,sort,pid,status');

        $builder
            ->title('文章分类管理')
            ->suggest('禁用、删除分类时会将分类下的文章转移到默认分类下')
            ->buttonNew(Url('add'))
            ->data($tree)
            ->display();
    }

    /**
     * 分类添加
     * @param int $id
     * @param int $pid
     */
    public function add($id = 0, $pid = 0)
    {
        $title=$id?"编辑":"新增";
        if (request()->isPost()) {
            $data = input();
            if($data['id']){
                $res = model('ArticlesCategory')->save($data,['id'=>$data['id']]);
            }else{
                $res = model('ArticlesCategory')->save($data);
            }
            if ($res) {
                cache('SHOW_EDIT_BUTTON',null);
                $this->success($title.'成功。', Url('category'));
            } else {
                $this->error($title.'失败!'.model('ArticlesCategory')->getError());
            }
        } else {
            $data = [];
            if ($id != 0) {
                $data = model('ArticlesCategory')->find($id);
            } else {
                $father_category_pid=model('ArticlesCategory')->where(['id'=>$pid])->value('pid');
                if($father_category_pid!=0){
                    $this->error('分类不能超过二级！');
                }
            }
            if($pid!=0){
                $categorys = model('ArticlesCategory')->where(['pid'=>0,'status'=>['egt',0]])->select();
            }else{
                $categorys = [];
            }
            $opt = [];
            foreach ($categorys as $category) {
                $opt[$category['id']] = $category['title'];
            }

            $builder = new AdminConfigBuilder();
            $builder
                ->title($title.'分类')
                ->data($data)
                ->keyId()
                ->keyText('title', '分类名')
                ->keySelect('pid', '父分类', '选择父级分类', ['0' => '顶级分类'] + $opt)
                ->keyDefault('pid',$pid)
                ->keyRadio('can_post','前台是否可投稿','',[0=>'否',1=>'是'])
                ->keyDefault('can_post',1)
                ->keyRadio('need_audit','前台投稿是否需要审核','',[0=>'否',1=>'是'])
                ->keyDefault('need_audit',1)
                ->keyInteger('sort','排序')
                ->keyDefault('sort',1)
                ->keyStatus()
                ->keyDefault('status',1)
                ->buttonSubmit(Url('add'))
                ->buttonBack()
                ->display();
        }
    }

    public function setStatus(){
        $ids = input('ids/a');
        $status = input('status');
        !is_array($ids)&&$ids=explode(',',$ids);
        if(in_array(1,$ids)){
            $this->error('id为 1 的分类是网站基础分类，不能被禁用、删除！');
        }
        $builder = new AdminTreeListBuilder();
        $builder->doSetStatus('ArticlesCategory', $ids, $status);
    }

}