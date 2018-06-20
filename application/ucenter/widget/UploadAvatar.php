<?php

namespace app\ucenter\widget;

require_once(VENDOR_PATH.'PHPImageWorkshop/ImageWorkshop.php');
use think\Controller;
use think\Db;
use PHPImageWorkshop\Core\ImageWorkshopLayer;
use PHPImageWorkshop\ImageWorkshop;

class UploadAvatar extends Controller
{
    public function render($uid = 0)
    {
        $this->assign('user', query_user(array('avatar256', 'avatar128', 'avatar64','avatar32','avatar512'), $uid));
        $this->assign('uid', $uid);
        return $this->fetch('ucenter@widget/uploadavatar');
    }


    public function getAvatar($uid = 0, $size = 256)
    {
        $avatar = Db::name('avatar')->where(array('uid' => $uid, 'status' => 1, 'is_temp' => 0))->find();
        if ($avatar) {

            if($avatar['driver'] == 'local'){
                $avatar_path = "/Uploads/Avatar".$avatar['path'];
                return $this->getImageUrlByPath($avatar_path, $size);
            }else{
                $new_img = $avatar['path'];
                $name = get_addon_class($avatar['driver']);
                if (class_exists($name)) {
                    $class = new $name();
                    if (method_exists($class, 'thumb')) {
                        $new_img =  $class->thumb($avatar['path'],$size,$size);
                    }
                }
                return $new_img;
            }
        } else {
            //如果没有头像，返回默认头像
            if($uid==session('temp_login_uid')||$uid==is_login()){
                $role_id = session('temp_login_role_id') ? session('temp_login_role_id') : get_role_id();
            }else{
                $role_id=query_user('show_role',$uid);
            }
            return $this->getImageUrlByRoleId($role_id, $size);
        }
    }




    private function getImageUrlByPath($path, $size,$isReplace = true)
    {
        $thumb = getThumbImage($path, $size, $size, 0, $isReplace);
        return get_pic_src($thumb['src']);
    }

    /**
     * 根据角色获取默认头像
     * @param $role_id
     * @param $size
     * @return mixed|string
     */
    private function getImageUrlByRoleId($role_id, $size)
    {
        $avatar_id=cache('Role_Avatar_Id_'.$role_id);
        if(!$avatar_id){
            $map = getRoleConfigMap('avatar', $role_id);
            $avatar_id = db('RoleConfig')->where($map)->field('value')->find();
            cache('Role_Avatar_Id_'.$role_id,$avatar_id,600);
        }
        if ($avatar_id) {
            if ($size != 0) {
                $path=getThumbImageById($avatar_id['value'], $size, $size);
            }else{
                $path=getThumbImageById($avatar_id['value']);
            }
        }else{//角色没有默认
            if ($size != 0) {
                $default_avatar = "/static/common/images/default_avatar.jpg";
                $path=$this->getImageUrlByPath($default_avatar, $size, false);
            } else {
                $path= get_pic_src("/static/common/images/default_avatar.jpg");
            }
        }
        return $path;
    }

    public function cropPicture($crop = null,$path)
    {
        //如果不裁剪，则发生错误
        if (!$crop) {
            $this->error('必须裁剪');
        }
        $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');
        if (strtolower($driver) == 'local') {
            //解析crop参数
            $crop = explode(',', $crop);
            $x = $crop[0];
            $y = $crop[1];
            $width = $crop[2];
            $height = $crop[3];
            //本地环境
            $image = ImageWorkshop::initFromPath($path);
            //生成将单位换算成为像素
            $x = $x * $image->getWidth();
            $y = $y * $image->getHeight();
            $width = $width * $image->getWidth();
            $height = $height * $image->getHeight();
            //如果宽度和高度近似相等，则令宽和高一样
            if (abs($height - $width) < $height * 0.01) {
                $height = min($height, $width);
                $width = $height;
            }
            //调用组件裁剪头像
            $image = ImageWorkshop::initFromPath($path);
            $image->crop(ImageWorkshopLayer::UNIT_PIXEL, $width, $height, $x, $y);
            $image->save(dirname($path), basename($path));
            //返回新文件的路径
            return  cut_str('/Uploads/Avatar',$path,'l');
        }else{
            $name = get_addon_class($driver);
            $class = new $name();
            $new_img = $class->crop($path,$crop);
            return $new_img;
        }
    }
} 