<?php
namespace app\api\controller;

use think\Controller;
use think\Db;
/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */

class File extends Controller
{
    /* 图片上传 */
    public function uploadPicture()
    {   
        $config = config('upload.image');
        /* 调用文件上传组件上传文件 */
        $files = request()->file();

        foreach($files as $file){
            if (empty($file)) {
                $this->error('No file upload or server upload limit exceeded');
            }

            //判断是否已经存在附件
            $sha1 = $file->hash();
            //处理已存在图片
            if($sha1){
                $pic_info = Db::name('Picture')->where(['sha1'=>$sha1])->find();
                if($pic_info){
                    $return['data'][] = $pic_info;
                    continue;
                }
            }
            //初始化回传信息
            $return = [];
            //获取上传驱动
            $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');
            $driver = check_driver_is_exist($driver);
            //构建返回数据
            
            if($driver == 'local'){
                $info = $file->validate(['size'=>$config['maxsize'],'ext'=>$config['mimetype']])->move($config['savepath']);

                if($info){
                    // 成功上传后 获取上传信息
                    $data['path'] = DS . 'uploads'  . DS . 'picture'  . DS . $info->getSaveName();
                    $data['path'] = str_replace("\\","/",$data['path']);
                    $data['md5'] = $info->md5();
                    $data['sha1'] = $info->sha1();
                    $data['create_time'] = time();
                    $data['status'] = 1;
                    $data['driver'] = $driver;
                }else{
                    $return['code'] = 0;
                    $return['msg'] = $file->getError();
                }
            }else{
                //获取驱动配置
                $uploadConfig = get_upload_config($driver);
                //文件本地路径
                $filePath = $file->getRealPath();
            }

            //写入数据库
            $id = Db::name('Picture')->insertGetId($data);
            if($id){
                $data['id'] = $id;
                $return['data'][] = $data;  
            }
        }
        //如果是单文件上传，直接显示数据的单独key
        if(is_array($return['data']) && count($return['data'])==1) {
            $return['file'] = $return['data'][0];
        }

        $return['code'] = 1;
        $return['msg'] = 'Upload successful';
        return json($return);

    }

    /* 文件上传 */
    public function uploadFile()
    {   
        $config = config('upload.file');
        /* 调用文件上传组件上传文件 */
        $files = request()->file();
        foreach($files as $file){
            if (empty($file)) {
                $this->error('No file upload or server upload limit exceeded');
            }
            
            //判断是否已经存在附件
            $sha1 = $file->hash();
            //处理已存在图片
            if($sha1){
                $file_info = Db::name('File')->where(['sha1'=>$sha1])->find();

                if($file_info){
                    $return['data'][] = $file_info;
                    continue;
                }
            }
            //初始化回传信息
            $return = [];
            //获取上传驱动
            $driver = modC('DOWNLOAD_UPLOAD_DRIVER','local','config');
            $driver = check_driver_is_exist($driver);
            //构建返回数据
            $data['driver'] = $driver;

            if($driver == 'local'){
                $info = $file->validate(['size'=>$config['maxsize'],'ext'=>$config['mimetype']])->move($config['savepath']);
                if($info){
                    // 成功上传后 获取上传信息
                    $data['name'] = $info->getInfo()['name'];
                    $data['mime'] = $info->getMime();
                    $data['size'] = $info->getInfo()['size'];
                    $data['savepath'] = DS . 'uploads'  . DS . 'file'  . DS . $info->getSaveName();
                    $data['savepath'] = str_replace("\\","/",$data['savepath']);
                    $data['savename'] = str_replace("\\","/",$info->getSaveName());
                    $data['ext'] = substr(strrchr($data['savename'], '.'), 1);
                    $data['md5'] = $info->md5();
                    $data['sha1'] = $info->sha1();
                    $data['create_time'] = time();

                }else{
                    $return['code'] = 0;
                    $return['msg'] = $file->getError();
                    return json($return);
                }
            }else{
                //获取驱动配置
                $uploadConfig = get_upload_config($driver);
                //文件本地路径
                $filePath = $file->getRealPath();
            }

            //写入数据库
            $id = Db::name('file')->insertGetId($data);
            if($id){
                $data['id'] = $id;
                $return['code'] = 1;
                $return['msg'] = 'Upload successful';
                $return['data'][] = $data;
            }
        }

        //如果是单文件上传，直接显示数据的单独key
        if(is_array($return['data']) && count($return['data'])==1) {
            $return['file'] = $return['data'][0];
        }

        $return['code'] = 1;
        $return['msg'] = 'Upload successful';
        return json($return);
    }
    /**
     * 用户头像上传
     * @return [type] [description]
     */
    public function uploadAvatar(){

        $aUid = is_login();

        /* 调用文件上传组件上传文件 */
        $file = request()->file('file');

        if (empty($file)) {
            $this->error('No file upload or server upload limit exceeded');
        }
        $return = [];
        //获取上传驱动
        $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');
        $driver = check_driver_is_exist($driver);
        //构建返回数据
        $data['driver'] = $driver;
        $data['uid'] = $aUid;
        if($driver == 'local'){
            $info = $file
            ->validate(['size'=>15678000,'ext'=>'jpg,png,gif'])
            ->rule('uniqid')
            ->move(ROOT_PATH . 'public' . DS . 'uploads'  . DS . 'avatar' . DS . $aUid);

            if($info){
                // 成功上传后 获取上传信息
                $data['path'] = DS . 'uploads'  . DS . 'avatar' . DS . $aUid . DS . $info->getSaveName();
                $return['code'] = 1;
                $return['msg'] = 'Upload successful';
                $return['data'] = $data;
            }else{
                $return['code'] = 0;
                $return['msg'] = $file->getError();
            }
        }else{
            //获取驱动配置
            $uploadConfig = get_upload_config($driver);
            //文件本地路径
            $filePath = $file->getRealPath();
        }
        
        //返回
        return json($return);
    }

}
