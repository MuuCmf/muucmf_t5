<?php

return [

    // 视图输出字符串内容替换,留空则会自动进行计算
    'view_replace_str'       => [
    	'__ZUI__'       => STATIC_URL . '/common/lib/zui-1.9.0',
    	'__COMMON__'    => STATIC_URL . '/common',
        '__LIB__'       => STATIC_URL . '/common/lib',
        '__JS__'    	=> STATIC_URL . '/index/js',
        '__IMG__'       => STATIC_URL . '/index/images',
        '__CSS__'       => STATIC_URL . '/index/css',   
    ],
];