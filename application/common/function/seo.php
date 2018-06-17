<?php

//读取SEO规则
function get_seo_meta($vars)
{

    //获取还没有经过变量替换的META信息
    $meta = model('common/SeoRule')->getMetaOfCurrentPage();

    //替换META中的变量
    foreach ($meta as $key => &$value) {
        $value = seo_replace_variables($value, $vars);
    }
    unset($value);

    //返回被替换的META信息
    return $meta;
}

function seo_replace_variables($string, $vars)
{
    //如果输入的文字是空的，那就直接返回空的字符串好了。
    if (!$string) {
        return '';
    }

    //调用ThinkPHP中的解析引擎解析变量
    $view = new Think\View();
    $view->assign('website_name',modC('WEB_SITE_NAME','MuuCmf','Config'));
    $view->assign($vars);
    $result = $view->fetch('', $string);

    //返回替换变量后的结果
    return $result;
}