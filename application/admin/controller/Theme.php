<?php
namespace app\admin\controller;

class Theme extends Admin
{
    /**
     * 主题列表页
     * 暂不处理，留给下一版本完善
     */
    public function tpls()
    {
        $aCleanCookie = input('get.cleanCookie', 0, 'intval');
        if ($aCleanCookie) {
            cookie('TO_LOOK_THEME', null, array('prefix' => 'MUUCMF'));
        }
        // 根据应用目录取全部主题信息
        $dir = MUUCMF_THEME_PATH;

        /*刷新模块列表时清空缓存*/
        $aRefresh = input('get.refresh', 0, 'intval');
        if ($aRefresh == 1) {
        } else if ($aRefresh == 2) {
            cache('admin_themes', null);
        }

        $tpls = cache('admin_themes');
        if ($tpls === false) {
            $tpls = null;
            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        //去掉"“.”、“..”以及带“.xxx”后缀的文件
                        if ($file != "." && $file != ".." && !strpos($file, ".")) {
                            if (is_file(MUUCMF_THEME_PATH . $file . '/info.php')) {
                                $tpl = require_once(MUUCMF_THEME_PATH . $file . '/info.php');
                                $tpl['path'] = MUUCMF_THEME_PATH . $file;
                                $tpl['file_name'] = $file;
                                $tpl['token']=file_get_contents(MUUCMF_THEME_PATH . $file . '/token.ini');
                                $tpls[] = $tpl;

                            }
                        }

                    }
                    closedir($dh);
                }
            }
            cache('admin_themes', $tpls);
        }
        

        $now_theme =  model('Theme')->getThemeValue('_THEME_NOW_THEME');
        $now_mtheme = model('Theme')->getThemeValue('_THEME_NOW_MTHEME');

        $this->meta_title = '主题列表';
        $this->assign('now_theme', $now_theme);
        $this->assign('now_mtheme', $now_mtheme);
        $this->assign('tplList', $tpls);
        return $this->fetch();
    }

    /**
     * 打包
     */
    public function packageDownload()
    {
        $aTheme = input('theme', '', 'text');
        if ($aTheme != '') {
            $themePath = MUUCMF_THEME_PATH;
            require_once("./ThinkPHP/Library/OT/PclZip.class.php");
            $archive = new \PclZip($themePath . $aTheme . '.zip');
            $data = $archive->create($themePath . $aTheme, PCLZIP_OPT_REMOVE_PATH, $themePath);
            if ($data) {
                $this->_download($themePath . $aTheme . '.zip', $aTheme . '.zip');
                return;
            } else {
                $this->error(lang('_PACKAGE_FAILURE_'));
                return;
            }
        }
        $this->error(lang('_PARAMETER_ERROR_'));
    }

    /**
     * 下载
     * @param $get_url
     * @param $file_name
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function _download($get_url, $file_name)
    {
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Transfer-Encoding: binary");
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . 'MuuCmf v1.0.0_Theme_' . $file_name);
        header('Content-Length: ' . filesize($get_url));
        error_reporting(0);
        readfile($get_url);
        flush();
        ob_flush();
        $this->_delFile($get_url);
        exit;
    }

    public function delete_theme()
    {
        $aTheme = input('theme', '', 'text');
        if ($aTheme != '') {
            $themePath = MUUCMF_THEME_PATH . $aTheme;
            $res = $this->_deldir($themePath);
            if ($res) {
                $this->success(lang('_DELETE_SUCCESS_'), Url('Admin/Theme/tpls'));
                return;
            } else {
                $this->error(lang('_DELETE_FAILED_'), Url('Admin/Theme/tpls'));
                return;
            }
        }
        $this->error(lang('_PARAMETER_ERROR_'), Url('Admin/Theme/tpls'));
    }

    /**
     * 设置网站使用主题
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function setTheme()
    {
        $item = input('post.item','all','text');
        $aTheme = input('post.theme', 'default', 'text');
        $themeModel = model('Common/Theme');
        if ($themeModel->setTheme($aTheme,$item)) {
            $result['info'] = lang('_SET_THE_THEME_TO_SUCCEED_');
            $result['status'] = 1;
        } else {
            $result['info'] = lang('_SET_THE_THEME_OF_FAILURE_');
            $result['status'] = 0;
        }
        $this->ajaxReturn($result);
    }

    /**
     * 临时查看主题（管理员预览用）
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function lookTheme()
    {
        $aTheme = input('theme', '', 'text');
        $themeModel = model('Common/Theme');
        $res=$themeModel->lookTheme($aTheme);
        if($res){
            redirect(Url('Home/Index/index'));
        }else{
            $this->error('请求失败！');
        }
    }

    public function add()
    {
        if (request()->isPost()) {
            $config = array(
                'maxSize' => 3145728,
                'rootPath' => MUUCMF_THEME_PATH,
                'savePath' => '',
                'saveName' => '',
                'exts' => array('zip', 'rar'),
                'autoSub' => true,
                'subName' => '',
                'replace' => true,
            );
            $upload = new \think\Upload($config); // 实例化上传类
            $info = $upload->upload($_FILES);
            if (!$info) { // 上传错误提示错误信息
                $this->error($upload->getError());
            } else { // 上传成功
                $this->_unCompression($info['pkg']['savename']);
                $this->success(lang('_INSTALLATION_SUCCESS_'), Url('Admin/Theme/tpls'));
            }
        } else {
            $this->display();
        }
    }

    private function _unCompression($filename)
    {
        $ThemePkg = MUUCMF_THEME_PATH;
        require_once("./ThinkPHP/Library/OT/PclZip.class.php");
        $pcl = new \PclZip($ThemePkg . $filename);
        if ($pcl->extract($ThemePkg)) {
            $result = $this->_delFile($ThemePkg . $filename);
            if ($result) {
                return true;
            }
        }
        return false;
    }

    private function _delFile($path)
    {
        $result = @unlink($path);
        if ($result) {
            return true;
        } else {
            return false;
        }

    }

    private function _deldir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->_deldir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
} 