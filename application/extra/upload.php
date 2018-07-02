<?php

//上传配置
return [

    /**
     * 文件保存格式
     */
    'savepath'   => '/uploads/{year}{mon}{day}/{filemd5}{.suffix}',
    /**
     * 最大可上传大小
     */
    'maxsize'   => '10mb',
    /**
     * 可上传的文件类型
     */
    'mimetype'  => 'jpg,png,bmp,jpeg,gif,zip,rar,xls,xlsx',
    /**
     * 是否支持批量上传
     */
    'multiple'  => false,
];
