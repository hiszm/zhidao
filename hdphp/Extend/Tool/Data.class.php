<?php
// .-----------------------------------------------------------------------------------
// |  Software: [HDPHP framework]
// |   Version: 2013.01
// |      Site: http://www.hdphp.com
// |-----------------------------------------------------------------------------------
// |    Author: 向军 <houdunwangxj@gmail.com>
// | Copyright (c) 2012-2013, http://houdunwang.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------
// |   License: http://www.apache.org/licenses/LICENSE-2.0
// '-----------------------------------------------------------------------------------

/**
 * 数据处理类
 * @package     tools_class
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
final class Data
{
    /**
     * 递归操作数组创建树状等级数组(可用于递归栏目操作)
     * @param $data 操作的数组
     * @param string $fieldPri 唯一键名，如果是表则是表的主键
     * @param string $fieldPid 父ID键名
     * @param int $pid 一级PID的值
     * @param string $sid 子ID用于获得指定指ID的所有父ID栏目
     * @param int $type 操作方式1=>返回多维数组,2=>返回一维数组,3=>得到指定子ID(参数$sid)的所有父栏目
     * @param string $html 栏目名称前缀，用于在视图中显示层次感的栏目列表
     * @param int $level 不需要传参数（执行时调用）
     * @return array
     */
    static public function channel($data, $fieldPri = 'cid', $fieldPid = 'pid', $pid = 0, $sid = null, $type = 1, $html = "&nbsp;", $level = 1)
    {
        if (!$data) {
            return array();
        }
        switch ($type) {
            case 1:
                $arr = array();
                foreach ($data as $v) {
                    if ($v[$fieldPid] == $pid) {
                        $arr[$v[$fieldPri]] = $v;
                        $arr[$v[$fieldPri]]['html'] = str_repeat($html, $level - 1);
                        $arr[$v[$fieldPri]]["Data"] = self::channel($data, $fieldPri, $fieldPid, $v[$fieldPri], $sid, $type, $html, $level + 1);
                    }
                }
                return $arr;
            case 2:
                $arr = array();
                $id = 0;
                foreach ($data as $v) {
                    if ($v[$fieldPid] == $pid) {
                        $arr[$id] = $v;
                        $arr[$id]['level'] = $level;
                        $arr[$id]['html'] = str_repeat($html, $level - 1);
                        $sArr = self::channel($data, $fieldPri, $fieldPid, $v[$fieldPri], $sid, $type, $html, $level + 1);
                        $arr = array_merge($arr, $sArr);
                        $id = count($arr);
                    }
                }
                return $arr;
            case 3:
                $arr = array();
                foreach ($data as $v) {
                    if ($v[$fieldPri] == $sid) {
                        $arr[] = $v;
                        $sArr = self::channel($data, $fieldPri, $fieldPid, $pid, $v[$fieldPid], $type, $html, $level + 1);
                        $arr = array_merge($arr, $sArr);
                    }
                }
                return ($arr);
        }
    }

    /**
     * 判断$s_cid是否是$d_cid的子栏目
     * @param $data 栏目数据
     * @param $s_cid 源栏目cid
     * @param $d_cid目标栏目cid
     * @param string $fieldPri
     * @param string $fieldPid
     */
    static function is_child($data, $s_cid, $d_cid, $fieldPri = 'cid', $fieldPid = 'pid')
    {
        $_data = self::channel($data, $fieldPri, $fieldPid, $s_cid, '', 2);
        foreach($_data as $c){
            //目标栏目为源栏目的子栏目
            if($c['cid']==$d_cid)return true;
        }
        return false;
    }

    /**
     * 递归实现迪卡尔乘积
     * @param $arr 操作的数组
     * @param array $tmp
     * @return array
     */
    static function descarte($arr, $tmp = array())
    {
        static $n_arr = array();
        foreach (array_shift($arr) as $v) {
            $tmp[] = $v;
            if ($arr) {
                self::descarte($arr, $tmp);
            } else {
                $n_arr[] = $tmp;
            }
            array_pop($tmp);
        }
        return $n_arr;
    }

}

?>