<?php
if (!defined("HDPHP_PATH"))
    exit('No direct script access allowed');
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
 * 关联模型
 * @package     Model
 * @subpackage  Driver
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
class RelationModel extends Model
{

    public $join = array(); //关联数据
    private $relation = array(); //关联模型数据，涵盖，主表，次表，显示字段，筛选方法(如where)等
    private $joinModel = array(); //只关联的模型

    /**
     * 是否执行关联
     * 关联方式 true 匹配所有关联（默认值）
     * @return bool
     */

    function __init()
    {
        if ($this->joinModel === false) { //没有任何模型操作
            return false;
        }
        if (empty($this->joinModel)) { //如果通过join()类方法，设置了要关联的模型时执行验证
            $this->joinModel = array_keys(array_change_key_case_d($this->join, 1));
        }
        foreach ($this->join as $table => $args) {
            if (!is_array($args)) {
                error(L("relationmodel_check_error0"), false);
            }
            if (!in_array(strtoupper($table), $this->joinModel)) { //关联的模型时执行验证
                continue;
            }
            //如果定义table属性,先执行table防止执行fields后再执行table,造成数据不准确
            if (isset($args['table'])) {
                $table = $args['table'];
                unset($args['table']);
                $args = array_merge(array("table" => $table), $args);
            }
            $args = array_change_key_case_d($args, 0); //将参数键名全发小写
            $this->check($args); //验证参数
            $args['type'] = strtoupper($args['type']);
            $args['as_table'] = isset($args['as']) ? $args['as'] : $table; //表别名
            $son_table_name = isset($args['table']) ? C("DB_PREFIX") . $args['table'] : C("DB_PREFIX") . $table; //子表表名
            $args['son_table'] = strtoupper($args['type'] == 'BELONGS_TO') ? $this->tableName : $son_table_name;
            $args['son_foreign_key'] = $this->getForeignKey($args); //获得从键
            $args['parent_table'] = strtoupper($args['type'] == 'BELONGS_TO') ? C("DB_PREFIX") . $table : $this->tableName; //获得主表
            $args['parent_key'] = $this->getParentKey($args); //获得主表的关联键
            $args['relationGroupsMethods'] = $this->relationGroupsMethods($args); //从表中的count,max,min
            $args['other'] = !isset($args['other']) ? false : ($args['other'] == true ? true : false); //是否显示额外项
            //以下是对MANY_TO_MANY的定义
            if ($args['type'] == "MANY_TO_MANY") {
                $args['relation_table'] = C("DB_PREFIX") . $args['relation_table'];
                $args['relation_table_parent_key'] = $args['relation_table_parent_key']; //主表在中间表中的字段
                $args['relation_table_foreign_key'] = $args['relation_table_foreign_key']; //从表在中间表中的字段
            }
            $this->relation[$table] = $args;
        }
        return true;
    }

    //验证参数
    private function check($args)
    {
        if (!isset($args['type']) || empty($args['type'])) {
            error(L("relationmodel_check_error1")); //多表操作定义的表的模型属性type值没有定义，如果不清楚使用规范，请参数HD框架帮助手册
        }
        $args['type'] = strtoupper($args['type']);
        if (!in_array($args['type'], array("HAS_ONE", "HAS_MANY", "BELONGS_TO", "MANY_TO_MANY"))) {
            error(L("relationmodel_check_error2")); //多表操作定义的表的模型属性type值必须是HAS_ONE、HAS_MANY、BELONGS_TO、MANY_TO_MANY中的一个，不区分大小写，如果不清楚使用规范，请参数HD框架帮助手册
        }
        if ($args['type'] == 'MANY_TO_MANY') {
            if (!isset($args['relation_table'])) {
                error(L("relationmodel_check_error3")); //使用多表操作MANY_TO_MANY的表没有定义relation_table属性即中间关联表，如果不清楚使用规范，请参数HD框架帮助手册
            }
            if (!isset($args['relation_table_parent_key']) || empty($args['relation_table_parent_key'])) {
                error(L("relationmodel_check_error4")); //使用多表操作MANY_TO_MANY的表没有定义relation_table_parent_key属性，如果不清楚使用规范，请参数HD框架帮助手册
            }
            if (!isset($args['relation_table_foreign_key']) || empty($args['relation_table_foreign_key'])) {
                error(L("relationmodel_check_error5")); //使用多表操作MANY_TO_MANY的表没有定义relation_table_foreign_key属性，如果不清楚使用规范，请参数HD框架帮助手册
            }
        }
    }

    /**
     * 关联表的分组统计方法 如count,max,min,avg方法
     * @param type $args
     * @return type
     */
    private function relationGroupsMethods($args)
    {
        $method = array(); //关联操作的所有方法
        foreach ($args as $k => $v) {
            if (in_array($k, array('count', 'max', 'min', 'avg'))) {
                $method[$k] = $v;
            }
        }
        return $method;
    }

    /**
     * 关联表的链式操作方法 如field、where、order等
     * @param type $table
     * @param type $args
     */
    private function relationMethods($table, $args)
    {
        $db = M($table);
        foreach ($args as $db_method => $db_args) {
            if (in_array($db_method, array_keys($db->db->opt))) {
                $db->$db_method($db_args);
            }
        }
    }

    //获得主表的关联键
    private function getParentKey($args)
    {
        if (isset($args['parent_key'])) {
            return $args['parent_key'];
        }
        $parent_db = M($args["parent_table"], true);
        if (!$parent_db->db->opt['pri']) {
            error($parent_db->tableName . L("relationmodel_get_parent_key")); //表的主键不存在，手动设置主表的主键或都指定模型的parent_key值试试，还不行就看手册学习一下吧，很简单的！
        }
        return $parent_db->db->opt['pri'];
    }

    //获得关联外键
    private function getForeignKey($args)
    {
        if (isset($args['foreign_key']) && !empty($args['foreign_key'])) {
            return $args['foreign_key'];
        }
        if ($args['type'] == 'MANY_TO_MANY') {
            $db = M($args["son_table"], true);
            if (!$db->db->opt['pri']) {
                error(L("relationmodel_get_foreign_key1")); //MANY_TO_MANY关联失败：2种解决方法，设置关联表的主键或都指定模型的foreign_key值
            }
            return $db->db->opt['pri'];
        }
        error(L("relationmodel_get_foreign_key2")); //定义关联模型必须指定foreign_key值，如果不清楚使用规范，请参数HD框架帮助手册
    }

    //只需要关联的模型，默认是关联所有模型
    public function join($type = false)
    {
        if ($type === true) {
            $this->joinModel = array();
            return $this;
        }
        if ($type === false) { //不关联任何模型
            $this->joinModel = false;
            return $this;
        }
        if (is_array($type)) {
            $join = $type;
        } else {
            $join = preg_replace("/\s/", '', $type);
            $join = preg_split("/[|,]/", $type);
        }
        foreach ($join as $v) {
            $this->joinModel[] = strtoupper($v);
        }
        return $this;
    }

    protected function reset()
    {
        $this->db->optInit(); //主模型查询参数归位
        $this->joinModel = array(); //关联模型复位
    }

    public function select()
    {
        $join_model = $this->__init();
        $parent_args = func_get_args();
        $parent_result = call_user_func(array($this->db, __FUNCTION__), $parent_args); //主表结果集
        //没有结果集或没有关联模型
        if ($join_model === false || !$parent_result)
            return $parent_result;
        $result = array(); //关联查询后的结果集
        foreach ($parent_result as $k => $v) {
            $result[$k] = $v;
            foreach ($this->relation as $table => $args) {
                $db = M($table); //关联表表模型
                $this->relationMethods($table, $args); //执行从表SQL如field、where
                switch ($args['type']) {
                    case "HAS_ONE":
                    case "HAS_MANY":
                        if (!isset($v[$args['parent_key']])) {
                            error(L("relationmodel_select")); //模型的parent_key属性定义错误,可能不存在此字段,或者主表结果集中不含parent_key字段
                        }
                        $db->where($args['foreign_key'] . '=' . $v[$args['parent_key']]);
                        if (!empty($args['relationGroupsMethods'])) { //执行模型定义的CURD 如count max avg
                            foreach ($args['relationGroupsMethods'] as $methodName => $methodArgs) {
                                $son_result = $db->$methodName($methodArgs);
                            }
                        } else {
                            $son_result = $db->all();
                        }
                        if (!$args['other'] && !$son_result) { //如果没有子表记录，且other为假，删除主表记录
                            unset($result[$k]);
                            break 2;
                        }
                        if ($args['type'] == 'HAS_ONE') {
                            if (!is_array($son_result)) {
                                $result[$k][$args['as_table']] = $son_result;
                            } else {
                                $son_result = isset($son_result[0]) && is_array($son_result[0]) ? $son_result[0] : $son_result;
                                $result[$k] = array_merge($result[$k], $son_result);
                            }
                        } else {
                            $result[$k][$args['as_table']] = $son_result;
                        }
                        break;
                    case "BELONGS_TO":
                        $parent = M($args["parent_table"], true); //子表为主表
                        if (empty($v[$args['son_foreign_key']])) { //结果中关联字段为空
                            if (!$args['other']) { //如果没有关联结果集，且设置不显示关联不成功数据时删除查找到的数据
                                unset($result[$k]);
                                break 2;
                            }
                            continue;
                        }
                        $parent->where($args['parent_key'] . '=' . $v[$args['son_foreign_key']]); //找到匹配的主表数据
                        if (!empty($args['relationGroupsMethods'])) { //执行模型定义的CURED 如count max avg
                            foreach ($args['relationGroupsMethods'] as $methodName => $methodArgs) {
                                $parent_result = $db->$methodName($methodArgs);
                            }
                        } else {
                            $parent_result = $parent->find();
                        }
                        if (!$args['other'] && !$parent_result) { //如果没有关联结果集，且设置不显示关联不成功数据时删除查找到的数据
                            unset($result[$k]);
                            break 2;
                        }
                        $result[$k][$args['as_table']] = $parent_result;
                        break;
                    case "MANY_TO_MANY":
                        $db = M($args['relation_table'], true); //关联中间表
                        $relation_table_info = $db->field($args['relation_table_foreign_key'])->where($args['relation_table_parent_key'] . '=' . $v[$args['parent_key']])->all();
                        $db = M($args["son_table"], true); //附表
                        if (!empty($relation_table_info)) {
                            foreach ($relation_table_info as $foreign_value) {
                                if (!empty($args['relationGroupsMethods'])) { //执行模型定义的CURED 如count max avg
                                    foreach ($args['relationGroupsMethods'] as $methodName => $methodArgs) {
                                        $relation_result = $db->$methodName($methodArgs);
                                    }
                                } else {
                                    $relation_result = $db->where($args['son_foreign_key'] . '=' . $foreign_value[$args['relation_table_foreign_key']])->findall();
                                }
                                if (!$args['other'] && !$relation_result) { //如果没有关联结果集，且设置不显示关联不成功数据时删除查找到的数据
                                    unset($result[$k]);
                                    break 2;
                                }
                                $result[$k][$args['as_table']][] = current($relation_result);
                            }
                            continue 2;
                        } else {
                            if (!$args['other']) { //如果没有关联结果集，且设置不显示关联不成功数据时删除查找到的数据
                                unset($result[$k]);
                                break 2;
                            }
                        }
                }
            }
        }
        $this->reset();
        return $result;
    }

    //数据插入
    public function insert()
    {
        $this->fieldMap(); //字段映射
        if ($this->validate() === false) { //自动验证
            return false;
        }
        $insert_data = $this->getArgs(func_get_args());
        if (empty($insert_data)) {
            if (!empty($_POST)) {
                $insert_data = array($_POST);
            } else {
                error(L("relationmodel_insert")); //悲剧了。。。执行INSERT()时没有任何插入数据，插入数据可以是$_POST也可以直接传入INSERT()方法中，HD框架手册能帮到你！
            }
        }
        $this->__init();
        $modelTableName = preg_replace("/" . C("DB_PREFIX") . "/", '', $this->tableName); //模型表名去表前缀
        $result = array(); //返回插入成功的ID 包括主从表
        $model_insert_id = call_user_func(array($this->db, __FUNCTION__), $insert_data);
        $result[$modelTableName] = $model_insert_id;
        if ($this->joinModel === false || !$model_insert_id) { //没有任何操作模型
            return $model_insert_id; //主表受影响的记录
        }
        foreach ($this->relation as $table => $args) {
            if (!isset($insert_data[0][$table])) { //不存在插入数据
                continue;
            }
            switch ($args['type']) {
                case "HAS_MANY":
                case "HAS_ONE":
                    $db = M($args['son_table'], true);
                    $son_insert_data = $insert_data[0][$table];
                    if (!is_array(current($son_insert_data))) { //子表插入的数据
                        $son_insert_data = array($son_insert_data);
                    }
                    foreach ($son_insert_data as $k => $v) {
                        $son_insert_data[$k][$args['foreign_key']] = $model_insert_id;
                    }
                    $result[$table] = $db->insert($son_insert_data);
                    break;
                case "BELONGS_TO":
                    $parent_db = M($args['parent_table'], true);
                    $parent_insert_id = $parent_db->insert($insert_data[0][$table]);
                    $result[$table] = $parent_insert_id;
                    $data = array();
                    $data[$args['foreign_key']] = $parent_insert_id;
                    $this->in($model_insert_id);
                    $this->db->update(array($data));
                    break;
                case "MANY_TO_MANY":
                    $son_db = M($args['son_table'], true);
                    $result[$table] = $son_db->insert($insert_data[0][$table]);
                    $relation_db = M($args['relation_table'], true);
                    $data = array($args['relation_table_parent_key'] => $result[$modelTableName], $args['relation_table_foreign_key'] => $result[$table]);
                    $result[$args['relation_table']] = $relation_db->insert($data);
                    break;
            }
        }
        $this->reset();
        return $result;
    }

    public function update()
    {
        if ($this->validate() === false) { //自动验证
            return false;
        }
        $update_data = $this->getArgs(func_get_args());
        if (empty($update_data)) {
            if (!empty($_POST)) {
                $update_data = array($_POST);
            } else {
                error(L("relationmodel_update")); //悲剧了。。。执行INSERT()时没有任何插入数据，插入数据可以是$_POST也可以直接传入INSERT()方法中，HD框架手册能帮到你！
            }
        }
        $this->__init();
        $modelTableName = preg_replace("/" . C("DB_PREFIX") . "/", '', $this->tableName); //模型表名去表前缀
        $result = array(); //返回更新成功的受影响条数据 包括主从表
        $model_update_affected = call_user_func(array($this->db, __FUNCTION__), $update_data);
        $result[$modelTableName] = $model_update_affected; //主模型受影响行数
        if ($this->joinModel === false) { //没有任何操作模型
            return $model_update_affected; //主表受影响的记录
        }
        $model_db = M($this->tableName, true); //主模型表对象
        $model_db->db->opt = $model_db->db->optOld; //主模型更新时的条件
        $model_update_id = $model_db->all(); //主模型更新的ID
        if (!$model_update_id)
            return 0;
        foreach ($this->relation as $table => $args) {
            if (!isset($update_data[0][$table])) { //不存在更数据，执行下个关联
                continue;
            }
            $relation_update_data = $update_data[0][$table]; //关联表更新数据
            switch ($args['type']) {
                case "HAS_MANY":
                case "HAS_ONE":
                    $foreign_id = array();
                    foreach ($model_update_id as $id) {
                        $foreign_id[] = $id[$args['parent_key']];
                    }
                    $db = M($args['son_table'], true);
                    $where = $args['foreign_key'] . ' in (' . implode(",", $foreign_id) . ")";
                    $result[$table] = $db->where($where)->update($relation_update_data);
                    break;
                case "BELONGS_TO":
                    $parent_id = array();
                    foreach ($model_update_id as $id) {
                        $parent_id[] = $id[$args['foreign_key']];
                    }
                    $parent_db = M($args['parent_table'], true);
                    $where = $args['parent_key'] . ' in (' . implode(",", $parent_id) . ")";
                    $result[$table] = $parent_db->where($where)->update($relation_update_data);
                    break;
                case "MANY_TO_MANY":
                    $relation_join_id = array(); //中间关联表
                    foreach ($model_update_id as $id) {
                        $relation_join_id[] = $id[$args['parent_key']];
                    }
                    $relation_join_db = M($args['relation_table'], true);
                    $where = $args['relation_table_parent_key'] . ' in (' . implode(",", $relation_join_id) . ")";
                    $relation_id = $relation_join_db->where($where)->field($args['relation_table_foreign_key'])->all();
                    $foreign_id = array(); //关联表在中间表中的外键，用于更新关联表的条件
                    foreach ($relation_id as $id) {
                        $foreign_id[] = $id[$args['relation_table_foreign_key']];
                    }
                    $son_db = M($args['son_table'], true);
                    $where = $args['son_foreign_key'] . ' in (' . implode(",", $foreign_id) . ")";
                    $result[$table] = $son_db->where($where)->update($relation_update_data);
                    break;
            }
        }
        $this->reset();
        return $result;
    }

    public function delete()
    {
        $delete_data = $this->getArgs(func_get_args());
        $this->__init();
        $result = array(); //返回插入成功的ID 包括主从表
        $this->db->cache(0); //查询不缓存
        $model_delete_id = call_user_func(array($this->db, 'select'), $delete_data);
        $modelTableName = preg_replace("/" . C("DB_PREFIX") . "/", '', $this->tableName); //模型表名去表前缀
        $this->db->opt = $this->db->optOld; //主模型更新时的条件
        $model_db = M($this->tableName, true); //主模型表对象
        $model_delete_affected = $model_db->delete();
        $result[$modelTableName] = $model_delete_affected;
        if ($this->joinModel === false || !$model_delete_affected) { //没有任何操作模型
            return $model_delete_affected; //主表受影响的记录
        }
        foreach ($this->relation as $table => $args) {
            switch ($args['type']) {
                case "HAS_MANY":
                case "HAS_ONE":
                    $foreign_id = array();
                    foreach ($model_delete_id as $id) {
                        $foreign_id[] = $id[$args['parent_key']];
                    }
                    $db = M($args['son_table'], true);
                    $where = $args['foreign_key'] . ' in (' . implode(",", $foreign_id) . ")";
                    $result[$table] = $db->where($where)->delete();
                    break;
                case "BELONGS_TO":
                    break;
//                    $parent_id = array();
//                    foreach ($model_delete_id as $id) {//主模型中删除的ID
//                        $parent_id[] = $id[$args['foreign_key']];
//                    }
//                    $parent_db = M($args['parent_table'], true);
//                    $where = $args['parent_key'] . ' in (' . implode(",", $parent_id) . ")";
//                    $result[$table] = $parent_db->where($where)->delete();
                    break;
                case "MANY_TO_MANY":
                    $join_id = array(); //组合中间表删除的条件
                    foreach ($model_delete_id as $id) { //主模型中删除的ID
                        $join_id[] = $id[$args['parent_key']];
                    }
                    $relation_table_db = M($args['relation_table'], true);
                    $where = $args['relation_table_parent_key'] . ' in (' . implode(",", $join_id) . ")";
                    $result[$table] = $relation_table_db->where($where)->delete();
                    break;
            }
        }
        $this->reset();
        return $result;
    }

    /**
     * 临时更改操作表
     * @param type $table   表名
     * @param type $full    是否带表前缀
     * @return Model
     */
    public function table($table, $full = false)
    {
        if (!$full) {
            $table = C("DB_PREFIX") . $table;
        }
        $this->db->table($table);
        $this->join(false);
        return $this;
    }

}

?>