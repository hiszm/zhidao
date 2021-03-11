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
 * Mysql数据库基类
 * @package     Db
 * @subpackage  Driver
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
abstract class Db implements DbInterface
{

    protected $table = null; //表名
    public $field; //字段字符串
    public $fieldArr; //字段数组
    public $lastquery; //最后发送的查询结果集
    public $pri = null; //默认表主键
    public $opt = array(); //SQL 操作
    public $optOld = array(); //旧的SQL操作
    protected $lastSql; //最后发送的SQL
    protected $cacheTime; //查询操作缓存时间单位秒
    protected $dbPrefix; //表前缀
    /**
     * 将eq等替换为标准的SQL语法
     * @var  array
     */
    protected $condition = array(
        "eq" => " = ", "neq" => " <> ",
        "gt" => " > ", "egt" => " >= ",
        "lt" => " < ", "elt" => " <= ",
    );

    /**
     * 数据库连接
     * 根据配置文件获得数据库连接对象
     * @param string $table
     * @return Object   连接对象
     */
    public function connect($table)
    {
        if (is_null($this->link)) {
            $this->link = $this->getLink(); //通过数据驱动如MYSQLI连接数据库
        }
        if (!is_null($table)) {
            $this->dbPrefix = C("DB_PREFIX"); //表前缀
            $this->table($table);
            $this->table = $table;
            $this->field = $this->opt['field'];
            $this->fieldArr = $this->opt['fieldArr'];
            $this->pri = $this->opt['pri'];
            $this->optReset(); //初始始化WHERE等参数
        } else {
            $this->optInit();
        }
        return $this->link;
    }

    /**
     * 初始化表字段与主键及发送字符集
     * @param string $tableName 表名
     */
    public function table($tableName)
    {
        if (is_null($tableName))
            return;
        $this->optInit();
        $field = $this->getFields($tableName); //获得表结构信息设置字段及主键属性
        $this->opt['table'] = $tableName;
        $this->opt['from_table'] = $tableName;
        $this->opt['pri'] = isset($field['pri']) && !empty($field['pri']) ? $field['pri'] : '';
        $this->opt['field'] = '`' . implode('` , ' . '`', $field['field']) . '`';
        $this->opt['fieldArr'] = $field['field'];
    }

    /**
     * 查询参数初始化
     * 每次执行curd后必须执行
     * @access protected
     */
    protected function optReset()
    {
        $this->optOld = $this->opt; //将opt赋值给旧的optOld属性
        $this->optOld['field'] = $this->field; //修改字段为全部字段
        $this->optInit(); //查询参数初始化
    }

    /**
     * 查询操作归位
     * @access public
     * @return void
     */
    public function optInit()
    {
        $this->cacheTime = NULL; //SELECT查询缓存时间
        $opt = array(
            'field' => $this->field,
            'fieldArr' => $this->fieldArr,
            'where' => '',
            'like' => '',
            'group' => '',
            'having' => '',
            'order' => '',
            'limit' => '',
            'in' => '',
            'Cache' => '',
            'table' => $this->table,
            'pri' => $this->pri,
            'from_table' => $this->table,
            "dataFormat" => null,
        );
        $this->opt = array_merge($this->opt, $opt);
    }

    /**
     * 获得表字段
     * @access public
     * @param string $tableName 表名
     * @return type
     */
    public function getFields($tableName)
    {
        $tableCache = $this->getCacheTable($tableName);
        $tableField = array();
        foreach ($tableCache as $v) {
            $tableField['field'][] = $v['field'];
            if ($v['key']) {
                $tableField['pri'] = $v['field'];
            }
        }
        return $tableField;
    }

    /**
     * 获得表结构缓存  如果不存在则生生表结构缓存
     * @access public
     * @param type $tableName
     * @return array    字段数组
     */
    private function getCacheTable($tableName)
    {
        $cacheName = C("DB_DATABASE") . $tableName;
        //字段缓存
        if (C("DB_FIELD_CACHE")) {
            $cacheTableField = F($cacheName, false, TABLE_PATH);
            if ($cacheTableField)
                return $cacheTableField;
        }
        //获得表结构
        $tableinfo = $this->getTableFields($tableName);
        $fields = $tableinfo['fields'];
        //字段缓存
        if (C("DB_FIELD_CACHE")) {
            F($cacheName, $fields, TABLE_PATH);
        }
        return $fields;
    }

    /**
     * 获得表结构及主键
     * 查询表结构获得所有字段信息，用于字段缓存
     * @access private
     * @param string $tableName
     * @return array
     */
    private function getTableFields($tableName)
    {
        $sql = "show columns from " . $tableName;
        $fields = $this->query($sql);
        if ($fields === false) {
            error("表{$tableName}不存在", false);
        }
        $n_fields = array();
        $f = array();
        foreach ($fields as $res) {
            $f ['field'] = $res ['Field'];
            $f ['type'] = $res ['Type'];
            $f ['null'] = $res ['Null'];
            $f ['field'] = $res ['Field'];
            $f ['key'] = ($res ['Key'] == "PRI" && $res['Extra']) || $res ['Key'] == "PRI";
            $f ['default'] = $res ['Default'];
            $f ['extra'] = $res ['Extra'];
            $n_fields [$res ['Field']] = $f;
        }
        $pri = '';
        foreach ($n_fields as $v) {
            if ($v['key']) {
                $pri = $v['field'];
            }
        }
        $info = array();
        $info['fields'] = $n_fields;
        $info['primarykey'] = $pri;
        return $info;
    }

    /**
     * 将查询SQL压入调试数组
     * @param void
     */
    protected function debug($sql)
    {
        $this->lastSql = $sql;
        if (DEBUG) {
            Debug::$sqlExeArr[] = $sql; //压入一条成功发送SQL
        }
    }

    /**
     * 查找记录,可以通过WHERE,ORDER,LIMIT等方法做限制
     * @access public
     * @param string $opt field字段列表 table表名 where条件 limit order排序
     * @return array|string
     */
    public function select($opt = '')
    {
        if (empty($this->opt['from_table']))
            error(L("mysql_select_error"), false); //"没有可操作的数据表"
        $opt = $this->formatArgs($opt);
        $where = $opt;
        if (!empty($opt)) {
            foreach ($opt as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $m => $n) {
                        if (method_exists($this, $m)) {
                            call_user_func(array($this, $m), array($n));
                            unset($where[$k]);
                        }
                    }
                }
            }
            if (!empty($where)) {
                $this->where($where);
            }
        }
        //添加表前缀
        $chain = array("where", "group", "having", "order", "limit");
        foreach ($chain as $v) {
            $this->opt[$v] = $this->addTableFix($this->opt[$v]);
        }
        $sql = "SELECT " . $this->opt['field'] . " FROM " . $this->opt['from_table'] .
            $this->opt['where'] . $this->opt['group'] . $this->opt['having'] .
            $this->opt['order'] . $this->opt['limit'];
        $dataFormat = $this->opt['dataFormat'];
        $data = $this->query($sql);
        //对数据应用函数
        if ($dataFormat['open']) {
            return data_format($data, $dataFormat['functions']);
        }
        return $data;
    }

    /**
     * 添加表前缀
     * @access public
     * @param string $sql
     * @return string   格式化后的SQL
     */
    public function addTableFix($sql)
    {
        $sqlRemoveSign = preg_replace(array("/`\s*/i"), array("`"), $sql);
        $sqlRemovePreFix = str_replace($this->dbPrefix, "", $sqlRemoveSign);
        return preg_replace("/\s+(`)?([a-z]\w+)(`)?([^@])?\.([a-z])/i", " \\1" . $this->dbPrefix . "\\2\\3\\4.\\5", $sqlRemovePreFix);
    }

    /**
     * 数据处理
     * @param void $opt  函数名
     */
    public function dataFormat($opt)
    {
        $opt = $this->formatArgs($opt);
        //如果没有数据格式化函数
        $functions = isset($opt[0]) ? $opt[0] : null;
        $this->opt['dataFormat'] = array("open" => true, "functions" => $functions);
    }

    /**
     * SQL中的REPLACE方法，如果存在与插入记录相同的主键或unique字段进行更新操作
     * @access  public
     * @param   mixed $opt
     * @return  mixed
     */
    public function insert($opt, $type = "insert")
    {
        $opt = $this->formatArgs($opt);
        if ($opt === false) {
            error(L("mysql_replace_error1"), false); //没有任何数据要插入,系统会将$_POST值自动插入，也可以手动将数据传入或者用ORM方式，请查看HD手册学习
        }
        if (!is_array(current($opt))) {
            $opt = array($opt);
        }
        $insert_id = array(); //插入的ID
        $pri = $this->opt['pri']; //主键，在使用table()函数时价值存在
        //对数据应用函数
        if ($this->opt['dataFormat']['open']) {
            $opt = data_format($opt, $this->opt['dataFormat']['functions']);
        }
        foreach ($opt as $k => $data) {
            //处理后数组的KEY均为数字
            if (is_string($k))
                continue;
            $value = $this->formatField($data);
            if (empty($value)) {
                error(L("mysql_replace_error2")); //插入数据错误，原因可能为1：插入内容为空   2：字段名非法，看一下HD框架手册吧！
            }
            $sql = strtoupper($type) . " INTO " . $this->opt['table'] . "(" . implode(',', $value['fields']) . ")" .
                "VALUES (" . implode(',', $value['values']) . ")";
            $insert_id[] = $this->exe($sql) ? $this->getInsertId() : false; //执行后的结果
        }
        //没有执行任何插入动作
        if (empty($insert_id)) {
            return false;
        }
        //没有主键
        if (!$pri) {
            return $this->getAffectedRows();
        }
        return count($insert_id) > 1 ? $insert_id : $insert_id[0];
    }

    /**
     * 更新数据
     * @access      public
     * @param  mixed $opt
     * @return mixed
     */
    public function update($opt)
    {
        if (empty($this->opt['table']))
            error(L("mysql_select_error"), false); //"没有可操作的数据表"
        $opt_res = $this->formatArgs($opt);
        if ($opt_res === false) {
            error(L("mysql_update_error1"), false); //没有任何数据要更新,系统会将$_POST值自动更新，也可以手动将数据传入或者用ORM方式，请查看HD手册学习
        }
        $opt = $opt_res[0];
        if (!is_array(current($opt))) {
            $opt = array($opt);
        }
        $affected = 0; //受影响记录数
        foreach ($opt as $k => $data) {
            if (is_string($k))
                continue;
            if (empty($this->opt['where'])) {
                if (isset($data[$this->opt['pri']])) {
                    $this->opt['where'] = " WHERE " . $this->opt['pri'] . " = " . intval($data[$this->opt['pri']]);
                } else {
                    error(L("mysql_update_error2"), false); //UPDATE更新语句必须输入条件,如果更新数据有表的主键字段也可以做为条件使用
                }
            }
            $value = $this->formatField($data);
            if (empty($value)) {
                error(L("mysql_update_error3"), false);
            }
            $sql = "UPDATE " . $this->opt['table'] . " SET ";
            foreach ($value['fields'] as $k => $v) {
                $sql .= $v . "=" . $value['values'][$k] . ',';
            }
            $sql = trim($sql, ',') . $this->opt['where'] . $this->opt['limit'];
            $affected += $this->exe($sql);
        }
        return $affected;
    }

    /**
     * 删除方法    传入ID可以是数组
     * @access public
     * @param mixed $id
     * @return mixed
     */
    public function delete($opt)
    {
        if (empty($this->opt['table']))
            error(L("mysql_select_error"), false); //"没有可操作的数据表"
        if (!empty($opt)) { //参数不为空时配置WHERE
            $this->where($opt);
        }
        if (empty($this->opt['where'])) {
            error(L("mysql_delete_error"), false); //DELETE删除语句必须输入条件,如果删除数据有表的主键字段也可以做为条件使用，还不清楚就看一下HD手册吧
        }
        $sql = "DELETE FROM " . $this->opt['table'] . $this->opt['where'] . $this->opt['limit'];
        $affected = $this->exe($sql);
        return $affected;
    }

    /**
     * count max min avg 共用方法
     * @param string $type  类型如count|avg
     * @param mixed $opt    参数
     * @return mixed
     */
    private function statistics($type, $opt)
    {
        if (empty($this->opt['from_table']))
            error(L("mysql_select_error"), false); //"没有可操作的数据表"
        $opt = $this->formatArgs($opt);
        $field_list = empty($this->opt['field']) ? '' : ',' . $this->opt['field']; //统计字段表示
        $field = ''; //分组参数
        if ($opt === false) { //无参数
            if (!empty($this->opt['pri'])) {
                $field = " $type(" . $this->opt['table'] . '.' . $this->opt['pri'] . ") " . " AS " . $this->opt['pri'];
            } elseif ($type == 'count') {
                $field = " $type(*) ";
            }
        } elseif (is_array($opt[0]) || (!$this->isField($opt[0]) && !strstr($opt[0], "|"))) { //参数为条件
            $this->where($opt);
            $field = " $type(" . $this->opt['table'] . '.' . $this->opt['pri'] . ") " . " AS " . $this->opt['pri'];
        } else {
            $opt = explode("|", $opt[0]);
            $as = isset($opt[1]) ? $opt[1] : $opt[0]; //别名
            $t = strstr($opt[0], ".") ? $opt[0] : $this->opt['table'] . '.' . $opt[0];
            $field = " $type(" . $t . ") " . " AS " . $as;
        }
        //如果不能组合成分组
        if (!$field)
            return;
        if (!empty($this->opt['group']) && !empty($this->opt['pri'])) {
            $field = $field . $field_list;
        }
        $this->opt['field'] = $field;
        $result = $this->select();
        return $result ? (count($result) > 1 ? $result : current($result[0])) : NULL;
    }

    /**
     * 统计记录总数
     * @access public
     * @param mixed 参数
     */
    public function count($opt)
    {
        return $this->statistics(__FUNCTION__, $opt);
    }

    /**
     * 查找最大的值
     * @access public
     * @param mixed 参数
     */
    public function max($opt = '')
    {
        return $this->statistics(__FUNCTION__, $opt);
    }

    /**
     * 查找最小的值
     * @access public
     * @param mixed 参数
     */
    public function min($opt = '')
    {
        return $this->statistics(__FUNCTION__, $opt);
    }

    /**
     * 查找平均值
     * @access public
     * @param mixed 参数
     */
    public function avg($opt = '')
    {
        return $this->statistics(__FUNCTION__, $opt);
    }

    /**
     * 得到标准的传递参数，统一转为数组
     * 去除参数中非空的值并以数组形式返回
     * @param mixed $opt
     * @return mixed
     */
    private function formatArgs($opt)
    {
        if (is_array($opt)) {
            if (empty($opt)) {
                return false;
            }
            $arr = array();
            foreach ($opt as $k => $v) {
                if (empty($v) && !is_numeric($v)) {
                    continue;
                }
                $arr[$k] = $v;
            }
            return empty($arr) ? false : $arr;
        }
        if (empty($opt)) {
            return false;
        }
        if (!is_array($opt)) {
            $opt = array($opt);
        }
        return $opt;
    }

    /**
     * 过滤非法字段
     * @param mixed $opt
     * @return array
     */
    public function fieldFilter($opt)
    {
        if (!$opt)
            return null;
        $field = array();
        foreach ($opt as $k => $v) {
            if ($this->isField($k))
                $field[$k] = $v;
        }
        return $field;
    }

    /**
     * 格式化SQL操作参数 字段加上标识符  值进行转义处理
     * @param array $vars   处理的数据
     * @return array
     */
    public function formatField($vars)
    {
        $data = array(); //格式化的数据
        if (!is_array($vars)) {
            return;
        }
        foreach ($vars as $k => $v) {
            if (!$this->isField($k)) { //字段非法
                continue;
            }
            $data['fields'][] = "`" . $k . "`";
            $data['values'][] = "\"" . addslashes_d($v) . "\"";
        }
        return $data;
    }

    /**
     * SQL查询条件
     * @param mixed $opt    链式操作中的WHERE参数
     * @return string
     */
    public function where($opt)
    {
        $opt = $this->formatArgs($opt);
        if ($opt === false) {
            return;
        }
        if (!strstr($this->opt['where'], 'WHERE')) {
            $this->opt['where'] .= " WHERE ";
        } else {
            $this->opt['where'] .= ' AND ';
        }
        $condition = array_keys($this->condition);

        foreach ($opt as $args) {
            //非数时where(8)|where("uid>2");
            if (!is_array($args)) {
                if (in_array(strtolower($args), array("or", "and"))) { //是否为or and
                    $this->opt['where'] = rtrim($this->opt['where'], " AND ");
                    $this->opt['where'] .= " " . strtoupper($args) . " ";
                } elseif (is_numeric($args)) {
                    $this->in($opt);
                    $this->opt['where'] .= " AND ";
                    break;
                } else {
                    $this->opt['where'] .= " $args " . " AND ";
                }
                continue;
            }
            foreach ($args as $k => $v) { //数组where(array("uid"=>array("gt"=>2)));
                if (is_array($v)) {
                    foreach ($v as $m => $n) {
                        if (in_array(strtolower($n), array("or", "and"))) { //是否为or|and
                            $this->opt['where'] = rtrim($this->opt['where'], " AND ");
                            $this->opt['where'] .= " " . strtoupper($n) . " ";
                            continue;
                        }
                        if (is_numeric($m)) { //值为数值
                            if (is_numeric($n)) {
                                ;
                                $this->in(array($k => $v));
                                continue 3;
                            }
                            if (is_string($n)) {
                                $this->opt['where'] .= " $n " . " AND ";
                            }
                            continue;
                        }
                        if (in_array(strtolower($m), $condition)) {
                            $n = is_numeric($n) ? $n : "'$n'";
                            $this->opt['where'] .= " `" . $k . "`" . $this->condition[$m] . $n . "
AND ";
                            continue;
                        }
                        $this->opt['where'] .= $this->opt['table'] . ".$k = '$n' " . " AND ";
                    }
                    continue;
                }
                if (in_array(strtolower($v), array("or", "and"))) { //是否为or and
                    $this->opt['where'] = rtrim($this->opt['where'], " AND ");
                    $this->opt['where'] .= " " . strtoupper($v) . " ";
                    continue;
                }
                if (is_numeric($k)) { //值为数值
                    if (is_numeric($v)) {
                        $this->in($args);
                        $this->opt['where'] .= " AND ";
                        continue 2;
                    }
                    if (is_string($v)) {
                        $this->opt['where'] .= " $v " . " AND ";
                    }
                    continue;
                }
                $v = is_numeric($v) ? $v : "'$v'";
                $this->opt['where'] .= $this->opt['table'] . ".$k = $v " . " AND ";
            }
        }
        $this->opt['where'] = rtrim($this->opt['where'], " AND ");
    }

    /**
     * in 语句
     * @param mixed $opt    链式操作中的参数
     * return string
     */
    public function in($opt)
    {
        $opt = $this->formatArgs($opt);
        if ($opt === false) {
            error(strtoupper(__FUNCTION__) . L("mysql_in_error"), false); //的参数不能为空，如果不清楚使用方式请查看HD手册学习
        }
        if (isset($opt[0]) && is_array($opt[0]))
            $opt = $opt[0];
        $in = '';
        foreach ($opt as $k => $v) {
            $field = is_numeric($k) ? $this->opt['pri'] : $k;
            $in = trim($in, ',');
            if (is_string($v)) {
                $v = trim($v);
                if (in_array(strtolower($v), array("or", "and"))) { //是否为or and
                    $this->opt['where'] = preg_replace("/(AND|OR)\s*$/", "", $this->opt['where']);
                    $this->opt['where'] .= " " . strtoupper($v) . " ";
                    continue;
                }
                $v = strstr($v, ',') ? explode(",", $v) : array($v); //解决字符传参1,2,3
            }
            if (is_numeric($v)) {
                $in .= ',' . $v . ',';
            }
            if (is_array($v)) {
                if (is_numeric(current($v))) {
                    $in .= "," . implode(",", $v) . ",";
                    continue;
                } elseif (is_array(current($v))) {
                    $this->in($v);
                    continue;
                } else {
                    $temp = array();
                    foreach ($v as $m => $n) {
                        $temp = array_merge($temp, explode(",", $n));
                    }
                    $inTemp = "";
                    foreach ($temp as $t) {
                        $inTemp .= is_numeric($t) ? $t . ',' : "'$t',";
                    }
                    $in .= "," . $inTemp;
                }
            }
        }
        $in = trim($in, ',');
        if (strchr($this->opt['where'], " WHERE ")) {
            $this->opt['where'] = str_replace(" WHERE ", '', $this->opt['where']);
        }
        if (empty($in) && $in != 0) {
            $this->opt['where'] = " WHERE " . $this->opt['where'];
        } else {
            if (!empty($this->opt['where'])) {
                if (!preg_match("/(AND|OR)\s*$/", $this->opt['where'])) {
                    $this->opt['where'] .= " AND ";
                }
            }
            $this->opt['where'] = " WHERE " . $this->opt['where'] . $field . " in(" . $in . ")";
        }
    }

    /**
     * 字段集
     * @access public
     * @param type $opt
     */
    public function field($opt)
    {
        $opt = $this->formatArgs($opt);
        if ($opt === false) {
            return;
        }
        $field = array();
        foreach ($opt as $v) {
            if (empty($v)) {
                return;
            }
            if (is_string($v)) {
                $v = explode(",", $v);
            }
            if (!is_array($v)) {
                continue;
            }
            foreach ($v as $n) {
                if (preg_match("/count|max|min|avg|sum/i", $n)) {
                    $field[] .= $n;
                    continue;
                }
                $n = str_replace(C("DB_PREFIX") . '.', ".", $n);
                $n = strstr($n, '.') ? '`' . $this->dbPrefix . str_replace(".", "`.`", $n) . '`' : '`' . $n . '`';
                $n = str_replace("|", '` AS `', $n);
                $field[] .= $n;
            }
        }
        $this->opt['field'] = implode(",", $field);
    }

    /**
     * limit 操作
     * @param type $opt
     * @return type
     */
    public function limit($opt)
    {
        $opt = $this->formatArgs($opt);
        if ($opt === false) {
            error(strtoupper(__FUNCTION__) . L("mysql_limit_error"), false); //的参数不能为空，如果不清楚使用方式请查看HD手册学习
        }
        foreach ($opt as $v) {
            if (is_array($v)) {
                $this->limit($v);
            } else {
                $this->opt['limit'] .= ',' . $v . ',';
            }
            if (strchr($this->opt['limit'], "LIMIT")) {
                $this->opt['limit'] = str_ireplace("LIMIT", '', $this->opt['limit']);
            }
            $this->opt['limit'] = trim($this->opt['limit'], ',');
        }
        $this->opt['limit'] = " LIMIT " . $this->opt['limit'];
    }

    /**
     * SQL 排序 ORDER
     * @param type $opt
     */
    public function order($opt)
    {
        $opt = $this->formatArgs($opt);
        if ($opt === false) {
            error(strtoupper(__FUNCTION__) . L("mysql_order_error"), false); // 的参数，如果不清楚使用方式请查看HD手册学习
        }
        foreach ($opt as $k => $v) {
            if (is_array($v)) {
                $this->order($v);
            } else {
                if (is_numeric($k)) {
                    $this->opt['order'] .= ',' . $v;
                } else {
                    $this->opt['order'] .= ',' . $k . " " . $v . ',';
                }
            }
            if (strchr($this->opt['order'], "ORDER BY")) {
                $this->opt['order'] = str_ireplace("ORDER BY", '', $this->opt['order']);
            }
            $this->opt['order'] = trim($this->opt['order'], ',');
        }
        $this->opt['order'] = " ORDER BY " . $this->opt['order'];
    }

    /**
     * 分组操作
     * @param type $opt
     */
    public function group($opt)
    {
        $opt = $this->formatArgs($opt);
        if ($opt === false) {
            error(strtoupper(__FUNCTION__) . L("mysql_group_error"), false); // 的参数，如果不清楚使用方式请查看HD手册学习
        }
        foreach ($opt as $v) {
            if (is_array($v)) {
                $this->group($v);
            } else {
                $this->opt['group'] .= ',' . $v . ',';
            }
            if (strchr($this->opt['group'], "GROUP BY")) {
                $this->opt['group'] = str_ireplace("GROUP BY", '', $this->opt['group']);
            }
            $this->opt['group'] = trim($this->opt['group'], ',');
        }
        $this->opt['group'] = " GROUP BY " . $this->opt['group'];
    }

    /**
     * 分组条件having
     * @param type $opt
     */
    public function having($opt)
    {
        $opt = $this->formatArgs($opt);
        foreach ($opt as $v) {
            if (is_array($v)) {
                $this->having($v);
            } else {
                $this->opt['having'] .= ' AND ' . $v . ' AND ';
            }
            if (strchr($this->opt['having'], "HAVING")) {
                $this->opt['having'] = str_ireplace("HAVING", '', $this->opt['having']);
            }
            $this->opt['having'] = trim($this->opt['having'], ' AND ');
        }
        $this->opt['having'] = " HAVING " . $this->opt['having'];
    }

    /**
     * 验证字段是否全法
     * @param type $field
     */
    protected function isField($field)
    {
        return is_string($field) && in_array($field, $this->opt['fieldArr']);
    }

    /**
     * 获得最后一条SQL
     * @return type
     */
    public function getLastSql()
    {
        return self::$lastSql;
    }

    /**
     * 获得所有SQL语句
     * @return type
     */
    public function getAllSql()
    {
        return Debug::$sqlExeArr;
    }

    /**
     * SELECT结果缓存时间
     * @return void
     */
    public function cache($time = null)
    {
        $this->cacheTime = is_int($time) ? $time : -1;
    }

    /**
     * 创建数据库
     * @param   string $dbName   数据库名
     * @param   string $charset  字符集
     * @return  boolean
     */
    public function createDatabase($dbName, $charset = null)
    {
        $charset = is_null($charset) ? CHARSET_DB : $charset;
        return $this->exe("CREATE DATABASE IF NOT EXISTS `$dbName` CHARSET " . $charset);
    }

    /**
     * 获得表信息
     * @param   string $dbName 数据库名
     * @param   string $tableName 表名
     * @return  array
     */
    public function getTableInfo($opt)
    {
        $opt = $this->formatArgs($opt);
        $tabArr = empty($opt) ? null : (is_array($opt[0]) ? $opt[0] : preg_split("/[,，\|]/", $opt[0])); //表名
        $tables = $this->query("SHOW TABLE STATUS FROM " . C("DB_DATABASE"));
        $arr = array();
        $arr['totalsize'] = 0; //总大小
        $arr['totalrow'] = 0; //总条数
        foreach ($tables as $k => $t) {
            if (!is_null($tabArr)) {
                if (!in_array($t['Name'], $tabArr)) {
                    continue;
                }
            }
            $arr['table'][$t['Name']]['tablename'] = $t['Name'];
            $arr['table'][$t['Name']]['engine'] = $t['Engine'];
            $arr['table'][$t['Name']]['rows'] = $t['Rows'];
            $arr['table'][$t['Name']]['collation'] = $t['Collation'];
            $charset = $arr['table'][$t['Name']]['collation'] = $t['Collation'];
            $charset = explode("_", $charset);
            $arr['table'][$t['Name']]['charset'] = $charset[0];
            $arr['table'][$t['Name']]['datafree'] = $t['Data_free'];
            $arr['table'][$t['Name']]['size'] = $t['Data_free'] + $t['Data_length'];
            $info = $this->getTableFields($t['Name']);
            $arr['table'][$t['Name']]['field'] = $info['fields'];
            $arr['table'][$t['Name']]['primarykey'] = $info['primarykey'];
            $arr['table'][$t['Name']]['autoincrement'] = $t['Auto_increment'] ? $t['Auto_increment'] : '';
            $arr['totalsize'] += $arr['table'][$t['Name']]['size'];
            $arr['totalrow']++;
        }
        return empty($arr) ? false : $arr;
    }

    /**
     * 修复数据表
     * @param  void $opt 表名
     * @return boolean
     */
    public function repair($opt)
    {
        $opt = $this->formatArgs($opt);
        $tabArr = empty($opt) ? null : (is_array($opt[0]) ? $opt[0] : preg_split("/[，,\|]/", $opt[0])); //表名
        foreach ($tabArr as $t) {
            $this->exe("REPAIR TABLE `" . $t . "`");
        }
        return true;
    }

    /**
     * 优化表解决表碎片问题
     */
    public function optimize($opt)
    {
        $opt = $this->formatArgs($opt);
        $tabArr = empty($opt) ? null : (is_array($opt[0]) ? $opt[0] : preg_split("/[，,\|]/", $opt[0])); //表名
        if (is_array($opt) && !empty($opt)) {
            foreach ($tabArr as $t) {
                $this->exe("OPTIMIZE TABLE `" . $t . "`");
            }
        }
        return true;
    }

    /**
     * 删除表
     */
    public function dropTable($opt)
    {
        $opt = $this->formatArgs($opt);
        $tabArr = empty($opt) ? null : (is_array($opt[0]) ? $opt[0] : preg_split("/[，,\|]/", $opt[0])); //表名
        foreach ($tabArr as $t) {
            $this->exe("DROP TABLE IF EXISTS `" . $t . "`");
        }
        return true;
    }

    //修改表名
    public function rename($opt)
    {
        $tabArr = empty($opt) ? null : (is_array($opt[0]) ? $opt[0] : $opt); //表名
        $oldName = $tabArr[0];
        $newName = $tabArr[1];
        foreach ($tabArr as $t) {
            if (strstr($oldName, $t)) {
                $new = str_replace($oldName, $newName, $t);
                $this->exe("ALTER TABLE `" . $t . "` RENAME " . $new);
            }
        }
        return true;
    }

    /**
     * 清空表
     * @param  string $tableName 表名
     * @param  string $full 是否为全表名，即带表前缀
     * @return boolean
     */
    public function truncate($opt)
    {
        $opt = $this->formatArgs($opt);
        $tabArr = empty($opt) ? null : (is_array($opt[0]) ? $opt[0] : preg_split("/[,，\|]/", $opt[0])); //表名
        if (is_null($tabArr))
            return false;
        foreach ($tabArr as $t) {
            $this->exe("TRUNCATE TABLE `" . $t . "`");
        }
        return true;
    }

    /**
     * 获得数据库或表大小
     * @param void $opt 表名
     * @return type
     */
    public function getSize($opt)
    {
        $tabArr = empty($opt) ? null : (is_array($opt[0]) ? $opt[0] : preg_split("/[,，\|]/", $opt[0])); //表名
        $tabName = array();
        if (!is_null($tabArr)) {
            foreach ($tabArr as $v) {
                $tabName[] = strtolower($v);
            }
        }
        $sql = "show table status from " . C("DB_DATABASE");
        $row = $this->query($sql);
        $size = 0;
        foreach ($row as $v) {
            if (!empty($tabName)) {
                $size += in_array(strtolower($v['Name']), $tabName) ? $v['Data_length'] + $v['Index_length'] : 0;
                continue;
            }
            $size += $v['Data_length'] + $v['Index_length'];
        }
        return get_size($size);
    }

    /**
     * 执行多条SQL语句
     * @param void $opt SQL语名
     * @return type
     */
    public function runSql($opt)
    {
        $opt = $this->formatArgs($opt);
        $sqlStrArr = empty($opt) ? null : (is_array($opt[0]) ? $opt[0] : $opt); //SQL
        $sqls = array(); //执行的sql
        $num = 0;
        foreach ($sqlStrArr as $str) {
            $str = str_replace("\r", "\n", $str);
            $sqlArr = explode(";\n", trim($str));
            foreach ($sqlArr as $sql) {
                $querys = explode("\n", trim($sql));
                $sqls[$num] = '';
                foreach ($querys as $query) {
                    $sqls[$num] .= $query[0] == '#' || $query[0] . $query[1] == '--' ? '' : $query;
                }
                $num++;
            }
        }
        unset($opt);
        unset($sqlStrArr);
        unset($sqlArr);
        unset($querys);
        foreach ($sqls as $sql) {
            $this->exe($sql);
        }
        return true;
    }

    /**
     * 字段值增加
     * 示例：$Db->dec("price","id=20",188)
     * 将id为20的记录的price字段值增加188
     * @return type
     */
    public function inc($opt)
    {
        $this->where(array($opt[1])); //条件
        $sql = "UPDATE " . $this->opt['table'] . " SET " . $opt[0] . '=' . $opt[0] . '+' . $opt[2] . $this->opt['where'];
        return $this->exe($sql);
    }

    /**
     * 减少字段值
     * 将id为4的记录的total字段值减8
     * <code>
     * 示例：$Db->dec("total","id=4",8)
     * </code>
     * @param mixed $opt
     * @return type
     */
    public function dec($opt)
    {
        $this->where(array($opt[1])); //条件
        $sql = "UPDATE " . $this->opt['table'] . " SET " . $opt[0] . '=' . $opt[0] . '-' . $opt[2] . $this->opt['where'];
        return $this->exe($sql);
    }

    /**
     * 判断字段是否存在于表中
     * @param string $fieldName 字段名
     * @param string $table 表名
     * @return bool
     */
    public function fieldExists($fieldName, $table)
    {
        $sql = "DESC " . C("DB_PREFIX") . $table;
        $field = $this->query($sql);
        foreach ($field as $f) {
            if ($f['Field'] == $fieldName) {
                return true;
            }
        }
        return false;
    }

}