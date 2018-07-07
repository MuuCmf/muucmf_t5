<?php
namespace app\common\model;

use think\model;

class File extends model;

    /**
     * 下载指定文件
     * @param  number  $root 文件存储根目录
     * @param  integer $id   文件ID
     * @param  string   $args     回调函数参数
     * @return boolean       false-下载失败，否则输出下载文件
     */
    public function download($root, $id, $callback = null, $args = null){
        /* 获取下载文件信息 */
        $file = $this->find($id);
        if(!$file){
            $this->error = L('_NO_THIS_FILE_IS_NOT_THERE_WITH_EXCLAMATION_');
            return false;
        }

        /* 下载文件 */
        if($file['driver'] == 'local'){
            $file['rootpath'] = $root;
            return $this->downLocalFile($file, $callback, $args);
        }else{
            redirect($file['savepath']);
        }

    }

    /**
     * 检测当前上传的文件是否已经存在
     * @param  array   $file 文件上传数组
     * @return boolean       文件信息， false - 不存在该文件
     */
    public function isFile($file){
        if(empty($file['md5'])){
            throw new \Exception('缺少参数:md5');
        }
        /* 查找文件 */
        $map = array('md5' => $file['md5'],'sha1'=>$file['sha1'],);
        return $this->field(true)->where($map)->find();
    }

    /**
     * 下载本地文件
     * @param  array    $file     文件信息数组
     * @param  callable $callback 下载回调函数，一般用于增加下载次数
     * @param  string   $args     回调函数参数
     * @return boolean            下载失败返回false
     */
    private function downLocalFile($file, $callback = null, $args = null){
        $path = $file['rootpath'].$file['savepath'].$file['savename'];
        if(is_file($path)){
            /* 调用回调函数新增下载数 */
            is_callable($callback) && call_user_func($callback, $args);

            /* 执行下载 */ //TODO: 大文件断点续传
            header("Content-Description: File Transfer");
            header('Content-type: ' . $file['type']);
            header('Content-Length:' . $file['size']);
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            }
            readfile($path);
            exit;
        } else {
            $this->error = L('_FILE_HAS_BEEN_DELETED_WITH_EXCLAMATION_');
            return false;
        }
    }

	/**
	 * 下载ftp文件
	 * @param  array    $file     文件信息数组
	 * @param  callable $callback 下载回调函数，一般用于增加下载次数
	 * @param  string   $args     回调函数参数
	 * @return boolean            下载失败返回false
	 */
	private function downFtpFile($file, $callback = null, $args = null){
		/* 调用回调函数新增下载数 */
		is_callable($callback) && call_user_func($callback, $args);

		$host = C('DOWNLOAD_HOST.host');
		$root = explode('/', $file['rootpath']);
		$file['savepath'] = $root[3].'/'.$file['savepath'];

		$data = array($file['savepath'], $file['savename'], $file['name'], $file['mime']);
		$data = json_encode($data);
		$key = think_encrypt($data, C('DATA_AUTH_KEY'), 600);

		header("Location:http://{$host}/onethink.php?key={$key}");
	}

	/**
	 * 清除数据库存在但本地不存在的数据
	 * @param $data
	 */
	public function removeTrash($data){
		$this->where(array('id'=>$data['id'],))->delete();
	}

}