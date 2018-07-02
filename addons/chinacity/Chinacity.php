<?php
namespace addons\chinaCity;

use app\common\controller\Addons;
use think\Db;


    class Chinacity extends Addons{

        public $info = array(
            'name'=>'Chinacity',
            'title'=>'中国省市区三级联动',
            'description'=>'每个系统都需要的一个中国省市区三级联动插件。想天-駿濤修改，将镇级地区移除',
            'status'=>1,
            'author'=>'muucmf',
            'version'=>'2.0'
        );

        public function install(){

            /* 先判断插件需要的钩子是否存在 */
            $this->getisHook('J_China_City', $this->info['name'], $this->info['description']);

            //读取插件sql文件
            $sqldata = file_get_contents($this->addon_path.'install.sql');

            $sqlFormat = $this->sql_split($sqldata, config('database.perfix'));
            $counts = count($sqlFormat);
            
            for ($i = 0; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
                Db::execute($sql);
            }
            return true;
        }

        public function uninstall(){
            //读取插件sql文件
            $sqldata = file_get_contents($this->addon_path.'uninstall.sql');

            $sqlFormat = $this->sql_split($sqldata, config('database.perfix'));
            $counts = count($sqlFormat);
             
            for ($i = 0; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
                Db::execute($sql);
            }
            return true;
        }

        //实现的J_China_City钩子方法
        public function J_China_City($param){
            $this->assign('param', $param);
            return $this->fetch('chinacity');
        }

        //获取插件所需的钩子是否存在
        public function getisHook($str, $addons, $msg=''){
            
            $where['name'] = $str;
            $gethook = Db::name('Hooks')->where($where)->find();
            if(!$gethook || empty($gethook) || !is_array($gethook)){
                $data['name'] = $str;
                $data['description'] = $msg;
                $data['type'] = 1;
                $data['update_time'] = time();
                $data['addons'] = $addons;
                
                Db::name('Hooks')->insert($data);
                
            }
        }

        /**
         * 解析数据库语句函数
         * @param string $sql  sql语句   带默认前缀的
         * @param string $tablepre  自己的前缀
         * @return multitype:string 返回最终需要的sql语句
         */
        public function sql_split($sql, $tablepre) {

            if ($tablepre != "muucmf_")
                $sql = str_replace("muucmf_", $tablepre, $sql);
                $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

            if ($r_tablepre != $s_tablepre)
                $sql = str_replace($s_tablepre, $r_tablepre, $sql);
                $sql = str_replace("\r", "\n", $sql);
                $ret = array();

                $num = 0;
                $queriesarray = explode(";\n", trim($sql));
                unset($sql);

            foreach ($queriesarray as $query) {
                $ret[$num] = '';
                $queries = explode("\n", trim($query));
                $queries = array_filter($queries);
                foreach ($queries as $query) {
                    $str1 = substr($query, 0, 1);
                    if ($str1 != '#' && $str1 != '-')
                        $ret[$num] .= $query;
                }
                $num++;
            }
            return $ret;
        }
    }