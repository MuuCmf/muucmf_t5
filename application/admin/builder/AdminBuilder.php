<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-12
 * Time: AM10:08
 */

namespace app\admin\builder;
use think\View;
use think\Controller;
use app\admin\model\AuthGroup;
use app\admin\Controller\Admin;

/**
 * AdminBuilder：快速建立管理页面。
 *
 * 为什么要继承AdminController？
 * 因为AdminController的初始化函数中读取了顶部导航栏和左侧的菜单，
 * 如果不继承的话，只能复制AdminController中的代码来读取导航栏和左侧的菜单。
 * 这样做会导致一个问题就是当AdminController被官方修改后AdminBuilder不会同步更新，从而导致错误。
 * 所以综合考虑还是继承比较好。
 *
 * Class AdminBuilder
 * @package Admin\Builder
 */
abstract class AdminBuilder extends Controller
{

    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        //获取模版的名称
        $template = dirname(__FILE__) . '/../View/builder/' . $templateFile . '.html';

        //显示页面
        echo $this->fetch($template);
    }

    protected function compileHtmlAttr($attr) {
        $result = array();
        
        if(is_array($attr)){
            foreach($attr as $key=>$value) {
                $value = htmlspecialchars($value);
                $result[] = "$key=\"$value\"";
            }
            $result = implode(' ', $result);
        }
        
        return $result;
    }
}

