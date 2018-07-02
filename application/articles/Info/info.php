<?php

return [
    //模块名
    'name' => 'articles',
    //别名
    'alias' => '文章',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '增强版文章模块，用户可前台投稿',
    //开发者
    'developer' => '北京火木科技有限公司',
    //开发者网站
    'website' => 'http://www.muucmf.cn',
    //前台入口，可用U函数
    'entry' => 'articles/index/index',
    //后台入口
    'admin_entry' => 'admin/Articles/index',
    //图标
    'icon' => 'th-list',
    //允许卸载
    'can_uninstall' => 1

];