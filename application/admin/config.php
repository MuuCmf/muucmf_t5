<?php

//配置文件
return [
    'url_common_param'       => true,
    'url_html_suffix'        => '',
    'controller_auto_search' => true,

    // 视图输出字符串内容替换,留空则会自动进行计算
    'view_replace_str'       => [
    	'__COMMON__'    => '/static/common',
        '__LIB__'       => '/static/common/lib',
        '__ZUI__'       => '/static/common/lib/zui',  
        '__JS__'    	=> '/static/admin/js',
        '__IMG__'       => '/static/admin/images',
        '__CSS__'       => '/static/admin/css',
        '__LIB__'       => '/static/admin/lib',
        '__PLUGIN__'       => '/static/admin/plugin',   
    ],
];
