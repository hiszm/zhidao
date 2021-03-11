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
 * 基本模型处理类
 * @package     Model
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
class Model
{

    protected $tableFull; //全表名
    protected $table; //不带前缀表名
    public $tableName; //表名  通过table方法得到的
    public $field; //表字段集
    public $join = array(); //多表关联
    public $db; //数据库连接驱动
    public $data = array(); //SQL 操作参数
    public $validate = null; //验证规则
    public $auto = null; //自动完成
    public $error; //验证不通过的错误信息
    public $map = array(); //字段映射

    /**
     * @param string $table 表名
     * @param bool $full 是否为全表名
     * @param string $driver 连接驱动
     */
    public function __construct($table = null, $full = null, $driver = null)
    {
        $this->tableName = $this->getTable($table, $full); //初始化默认表
        $this->db = DbFactory::factory($driver, $this->tableName); //获得数据库引擎
    }

    /**
     * 魔术方法  设置模型属性如表名字段名
     * @param string $var 属性名
     * @param mixed $value 值
     */
    public function __set($var, $value)
    {
        $var = strtolower($var);
        if (in_array($var, array('View', 'join'))) {
            return;
        }
        $property = array_keys($this->db->opt);
        if (in_array($var, $property)) {
            $this->$var($value);
        } else {
            $this->data[$var] = $value;
        }
    }

    /**
     * 字段映射
     */
    protected function fieldMap($data = null)
    {
        if (empty($this->map)) {
            return $data;
        }
        foreach ($this->map as $k => $v) {
            //处理POST
            if (isset($_POST[$k])) {
                $_POST[$v] = $_POST[$k];
                unset($_POST[$k]);
            }
            //处理自动验证
            if ($this->validate) {
                foreach ($this->validate as $validateName => $validateValue) {
                    if ($validateValue[0] == $k) {
                        $this->validate[$validateName][0] = $v;
                    }
                }
            }
            //传入数据时
            if ($data) {
                if (isset($data[$k])) {
                    $data[$v] = $data[$k];
                    unset($data[$k]);
                }
            }
        }
        return $data;
    }

    /**
     * 调用驱动方法
     * @param type $func
     * @param type $args
     * @return type
     */
    public function __call($func, $args)
    {
        if (!method_exists($this, $func)) {
            error(L("model__call_error") . $func, false); //模型中不存在方法
        }
    }

    /**
     * 设置SQL操作的参数
     * 参数经过以下几个步骤，由高到低
     * 1检测有无参数，有参数采用
     * 2如果参数是否为对象，如果是则检测有无data属性，有data属性则用data属性做为参数
     * 3检测对象有无data属性如果有将data属性设为参数
     * @param type $args    void
     * @return array
     */
    protected function getArgs($args)
    {
        if (is_object($args) && property_exists($args, 'Data')) { //对模型对象形式传参 $Db->Data="id>2"
            $args = array($args->data);
            $this->data = array();
        }
        if (empty($args) && !empty($this->data)) { //没有传递参数，
            $args = array($this->data);
            $this->data = array();
        }
        return $args;
    }

    //设置操作表
    protected function getTable($table = null, $full = false)
    {
        if (is_null($table)) {
            $table = null;
        } elseif (!empty($this->tableFull)) {
            $table = $this->tableFull;
        } elseif (!empty($this->table)) {
            $table = C("DB_PREFIX") . $this->table;
        } elseif (!empty($table)) {
            if ($full == true) {
                $table = $table;
            } else {
                $table = C("DB_PREFIX") . $table;
            }
        } else {
            $table = C("DB_PREFIX") . CONTROL;
        }
        return $table;
    }

    /**
     * 临时更改操作表
     * @param string $table 表名
     * @param bool $full 是否带表前缀
     * @return $this
     */
    public function table($table, $full = false)
    {
        if (!$full) {
            $table = C("DB_PREFIX") . $table;
        }
        $this->db->table($table);
        return $this;
    }

    /**
     * 设置字段
     * 示例：$Db->field("username,age")->limit(6)->all();
     */
    public function field()
    {
        $opt = func_get_args();
        if (empty($opt))
            return $this;
        call_user_func(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    /**
     * SQL中的WHERE规则
     * 示例：$Db->where("username like '%向军%')->count();
     */
    public function where()
    {
        $opt = $this->getArgs(func_get_args());
        if (empty($opt))
            return $this;
        call_user_func(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    /**
     * 执行查询操作结果不缓存
     * 示例：$Db->Cache(30)->all();
     */
    public function cache()
    {
        $opt = func_get_args();

        call_user_func_array(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    //SQL中的LIKE规则
    public function like()
    {
        $opt = $this->getArgs(func_get_args());
        if (empty($opt))
            return $this;
        call_user_func(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    /**
     * GROUP语句定义
     * 示例：$Db->having("id>2","age<20")->group("age")->all();
     */
    public function group()
    {
        $opt = $this->getArgs(func_get_args());
        if (empty($opt))
            return $this;
        call_user_func(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    /**
     * HAVING语句定义
     * 示例：$Db->having("id>2","age<20")->group("age")->all();
     */
    public function having()
    {
        $opt = $this->getArgs(func_get_args());
        if (empty($opt))
            return $this;
        call_user_func(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    /**
     * ORDER 语句定义
     * 示例：$Db->order("id desc")->all();
     */
    public function order()
    {
        $opt = $this->getArgs(func_get_args());
        if (empty($opt))
            return $this;
        call_user_func(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    /**
     * LIMIT 语句定义
     * 示例：$Db->limit(10)->all("sex=1");
     */
    public function limit()
    {
        $opt = $this->getArgs(func_get_args());
        if (empty($opt))
            return $this;
        call_user_func(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    /**
     * IN 语句定义
     * 示例：$Db->in(1,2,3)->all();
     */
    public function in()
    {
        $opt = $this->getArgs(func_get_args());
        if (empty($opt))
            return $this;
        call_user_func(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    /**
     * 删除记录
     * 示例：$Db->del("uid=1");
     */
    public function del()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this, 'delete'), $opt);
    }

    /**
     * 慎用  会删除表中所有数据
     * $Db->delall();
     */
    public function delall()
    {
        $opt = $this->getArgs(func_get_args());
        $this->db->where('1=1');
        return call_user_func_array(array($this, 'delete'), $opt);
    }

    /**
     * 删除记录
     * 示例：$Db->delete("uid=1");
     */
    public function delete()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 执行一个SQL语句  有返回值
     * 示例：$Db->query("select title,click,addtime from hd_news where uid=18");
     */
    public function query()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 执行一个SQL语句  没有有返回值
     * 示例：$Db->exe("delete from hd_news where id=16");
     */
    public function exe()
    {
        $opt = func_get_args();
        return call_user_func_array(array($this->db, 'exe'), $opt);
    }

    /**
     * 查找满足条件的一条记录
     * 示例：$Db->find("id=188")
     */
    public function find()
    {
        if (!$this->db->opt['limit']) {
            $this->db->opt['limit'] = " LIMIT 1 ";
        }
        $opt = func_get_args();
        $result = call_user_func_array(array($this, 'select'), $opt);
        return $result ? current($result) : $result;
    }

    /**
     * 查找满足条件的一条记录
     * 示例：$Db->one("id=188")
     */
    public function one()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this, 'find'), $opt);
    }

    /**
     * 查找满足条件的所有记录
     * 示例：$Db->findall("sex=1")
     */
    public function findall()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this, 'select'), $opt);
    }

    /**
     * 查找满足条件的所有记录
     * 示例：$Db->all("age>20")
     */
    public function all()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this, 'select'), $opt);
    }

    /**
     * 查找满足条件的所有记录
     * 示例：$Db->select("age>20")
     */
    public function select()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 查找满足条件的所有记录
     * 示例：$Db->getField("username")
     */
    public function getField()
    {
        $opt = $this->getArgs(func_get_args());
        //如果第2个参数为数字设置limit
        if (isset($opt[1]) && is_int($opt[1])) {
            $this->limit($opt[1]);
        }
        $data = $this->field($opt[0])->select();
        if (is_null($data))
            return null;
        $_result = array(); //返回结果
        //只有一个字段
        if (count($data[0]) == 1) {
            //第2个参数为true返回所有记录的
            if (isset($opt[1]) && $opt[1] === true) {

                foreach ($data as $d) {
                    $_result[] = current($d);
                }
                return $_result;
            }
            return current($data[0]);
        }
        //多个字段
        foreach ($data as $d) {
            $key = array_shift($d);
            $_result[$key] = $d;
        }
        return $_result;
        // return call_user_func(array($this->Db, __FUNCTION__), $opt);
    }

    //更新记录
    public function save()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this, 'update'), $opt);
    }

//添加数据
    public function update()
    {
        if ($this->validate() === false) { //自动验证
            return false;
        }
        $opt = $this->getArgs(func_get_args());
        if (empty($opt)) {
            if (!empty($_POST)) {
                $opt = array($_POST);
            } else {
                error(L("model_update_error"), false);
            }
        }
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

//插入数据
    public function insert()
    {
        //验证令牌
        if (!$this->tokenCheck()) {
            return false;
        }
        $args = func_get_args();
        $data = $this->getArgs(current($args));
        if (empty($data)) {
            if (!empty($_POST)) {
                $data = $_POST;
            } else {
                error(L("model_insert_error"), false); //悲剧了。。。INSERT参数不能为空！
            }
        }
        $mapData = $this->fieldMap($data); //字段映射
        //自动验证
        if ($this->validate($mapData) === false) {
            return false;
        }
        //自动完成
        $mapData = $this->auto($mapData, 1);
        $type = array_pop($args) == 'replace' ? "replace" : "insert";

        return call_user_func(array($this->db, __FUNCTION__), $mapData, $type);
    }

//插入数据
    public function replace()
    {
        $data = current(func_get_args());
        return call_user_func(array($this, "insert"), $data, "replace");
    }

//插入数据
    public function add()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this, 'insert'), $opt);
    }

    /**
     * 判断表中字段是否在存在
     * @param string $fieldName 字段名
     * @param string $table 表名(不带表前缀)
     * @return bool
     */
    public function fieldExists($fieldName,$table){
        return call_user_func_array(array($this->db,__FUNCTION__),array($fieldName,$table));
    }
    /**
     * 数据处理
     * 参数为要进行处理的函数字符串，可以传递多个函数
     * <code>
     * $Db = M("news");
     * $Db->dataFormat("strtoupper")->add()
     * </code>
     */
    public function dataFormat()
    {
        $opt = $this->getArgs(func_get_args());
        call_user_func(array($this->db, __FUNCTION__), $opt);
        return $this;
    }

    /**
     * 检索最大值
     * 参数可以传入SQL条件
     * 示例：$Db->max("sex=1")
     */
    public function max()
    {
        if (method_exists($this, "getJoin")) {
            $this->getJoin();
            $this->joinModel = true;
        }
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 检索最小值
     * 参数可以传入SQL条件
     * 示例：$Db->min("sex='girl'")
     */
    public function min()
    {
        if (method_exists($this, "getJoin")) {
            $this->getJoin();
            $this->joinModel = true;
        }
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 求平均址
     * 参数可以传入SQL条件
     * 示例：$Db->avg("id>100")
     */
    public function avg()
    {
        if (method_exists($this, "getJoin")) {
            $this->getJoin();
            $this->joinModel = true;
        }
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 统计记录数
     * 参数可以传入SQL条件
     * 示例：$Db->count("age>20")
     */
    public function count()
    {
        if (method_exists($this, "getJoin")) {
            $this->getJoin();
            $this->joinModel = true;
        }
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 删除表
     */
    public function dropTable($opt)
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 字段值增加
     * 示例：$Db->dec("price","id=20",188)
     * 将id为20的记录的price字段值增加188
     * @return type
     */
    public function inc()
    {
        $opt = $this->getArgs(func_get_args());
        if (count($opt) != 3) {
            error("inc方法参数不正确，示例：\$Db->dec('price','id=20',188)", false);
        }
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 过滤字段
     */
    public function fieldFilter()
    {
        $_opt = $this->getArgs(current(func_get_args()));
        $opt = $_opt ? $_opt : $_GET;
        foreach ($opt as $k)
            return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 减少字段值
     * 示例：$Db->dec("total","id=4",8)
     * 将id为4的记录的total字段值减8
     * @return type
     */
    public function dec()
    {
        $opt = $this->getArgs(func_get_args());
        if (count($opt) != 3) {
            error("DEC方法参数不正确，示例：\$Db->dec('total','id=4',8)", false);
        }
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 验证令牌
     */
    public function tokenCheck()
    {
        if (C("TOKEN_ON") || isset($_POST[C("TOKEN_NAME")]) || isset($_GET[C("TOKEN_NAME")])) {
            if (!Token::check()) {
                $this->error = L("model_token_error");
                return false;
            }
        }
        return true;
    }

    /**
     * 获得受影响的记录数
     */
    public function getAffectedRows()
    {
        return $this->db->getAffectedRows();
    }

    /**
     * 获得最后插入的ID
     */
    public function getInsertId()
    {
        return $this->db->getInsertId();
    }

    /**
     * 获得最后一条SQL
     */
    public function getLastSql()
    {
        return $this->db->getLastSql();
    }

    /**
     * 获得所有SQL
     */
    public function getAllSql()
    {
        return $this->db->getAllSql();
    }

//获得MYSQL版本
    public function getVersion()
    {
        return $this->db->getVersion();
    }

    //创建数据库
    public function createDatabase()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this->db, __FUNCTION__), $opt);
    }

    //获得数据库或表大小
    public function getSize()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 获得表信息
     * @param   void
     * @return  array
     */
    public function getTableInfo()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 清空表
     * @param   void
     * @return boolean
     */
    public function truncate()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * @param   void
     * 优化表解决表碎片问题
     */
    public function optimize()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 修复数据表
     * @param   void
     * @return boolean
     */
    public function repair($opt)
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 开启|关闭事务
     * @param bool $stat true开启事务| false关闭事务
     * @return mixed
     */
    public function beginTrans($stat = true)
    {
        return call_user_func(array($this->db, __FUNCTION__), $stat);
    }

    //批量修改表名
    public function rename()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    /**
     * 执行多条SQL语句
     * @param void 传入SQL字符串，也可以传入数组，每个元素为SQL字符串
     * @return type
     */
    public function runSql()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func(array($this->db, __FUNCTION__), $opt);
    }

    //提供一个事务
    public function commit()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this->db, __FUNCTION__), $opt);
    }

    //回滚事务
    public function rollback()
    {
        $opt = $this->getArgs(func_get_args());
        return call_user_func_array(array($this->db, __FUNCTION__), $opt);
    }

    //字段验证
    public function validate($data = null)
    {
        $data = is_null($data) ? array() : $data;
        $_data = array_merge($_POST, $data);
        if (is_null($this->validate)) {
            return true;
        }

        if (!is_array($this->validate)) {
            error(L("model_validate_error"), false); //验证规则定义错误，是不是打错了，看后盾帮助手册学习一下吧
        }
        foreach ($this->validate as $v) {
            $type = isset($v[3]) ? $v[3] : 1; //1 为默认验证方式    有POST这个变量就验证
            $name = $v[0]; //验证的表单名称
            $msg = $v[2]; //错误时的提示内容
            switch ($type) {
                //有post这个变量就验证
                case 1:
                    if (!isset($_data[$name])) {
                        continue 2;
                    }
                    break;
                // 必须验证
                case 2:
                    if (!isset($_data[$name])) {
                        $this->error = $msg;
                        return false;
                    }
                    break;
                //不为空验证
                case 3:
                    if (!isset($_data[$name]) || empty($_data[$name])) {
                        continue 2;
                    }
                    break;
            }
            $method = explode(":", $v[1]);
            $func = $method[0];
            $args = isset($method[1]) ? str_replace(" ", '', $method[1]) : '';
            if (method_exists($this, $func)) {
                $res = call_user_func_array(array($this, $func), array($name, $_data[$name], $msg, $args));
                if ($res === true) {
                    continue;
                }
                $this->error = $res;
                return false;
            } elseif (function_exists($func)) {
                $res = $func($name, $_data[$name], $msg, $args);
                if ($res === true) {
                    continue;
                }
                $this->error = $res;
                return false;
            } else {
                $validate = new Validate();
                $func = '_' . $func;
                if (method_exists($validate, $func)) {
                    $res = call_user_func_array(array($validate, $func), array($name, $_data[$name], $msg, $args));
                    if ($res === true) {
                        continue;
                    }
                    $this->error = $res;
                    return false;
                }
            }
        }
        return true;
    }

    //字段完成
    public function auto($data = null, $motion = 1)
    {
        $_data = $data;
        if (is_null($this->auto) || !is_array($_data)) {
            return $_data;
        }
        if (!is_array($this->auto)) {
            error(L("model_auto_error"));
        }
        foreach ($this->auto as $v) {
            $name = $v[0]; //验证的表单名称
            $action = $v[1]; //内容 string为内容   其他为function|method
            //1 插入时处理  2 更新时处理  3 插入与更新都处理
            $type = isset($v[2]) ? $v[2] : 1;
            //处理类型 function函数  method模型方法 string字符串
            $handle = isset($v[3]) ? $v[3] : "string";
            //是否处理  更新或插入
            if (!preg_match('@' . $type . '|3@', $motion)) {
                continue;
            }
            switch (strtolower($handle)) {
                case "function":
                    if (function_exists($action)) {
                        $_data[$name] = $action($_data[$name]);
                    }
                    break;
                case "method":
                    if (method_exists($this, $action)) {
                        $_data[$name] = call_user_func_array(array($this, $action), array($_data[$name]));
                    }
                    break;
                default :
                    $_data[$name] = $action;
            }
        }
        return $_data;
    }
}

?>