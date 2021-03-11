<?php
require_cache(HDPHP_DRIVER_PATH . '/Db/Db.class.php');
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
 * Mysql数据库驱动类
 * @package     Db
 * @subpackage  Driver
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
class DbMysql extends Db {

    static protected $db_link = null; //是否连接
    public $link = null; //数据库连接

    function getLink() {
        if (is_null(self::$db_link)) {
            $link = mysql_connect(C("DB_HOST"), C("DB_USER"), C("DB_PASSWORD"));
            if (!$link) {
                error(mysqli_connect_error() . L("mysqlidriver_connectDb"), false); //数据库连接出错了请检查配置文件中的参数
            } else {
                self::$db_link = $link;
                self::setCharts();
            }
        }
        $this->link = self::$db_link;
        mysql_select_db(C("DB_DATABASE"), $this->link);
        return $this->link;
    }

    /**
     * 设置字符集
     */
    static private function setCharts() {
        $character = C("CHARSET");
        $sql = "SET character_set_connection=$character,character_set_results=$character,character_set_client=binary";
        mysql_query($sql, self::$db_link);
    }

    //获得最后插入的ID号
    public function getInsertId() {
        return mysql_insert_id($this->link);
    }

    //获得受影响的行数
    public function getAffectedRows() {
        return mysql_affected_rows($this->link);
    }

    //遍历结果集(根据INSERT_ID)
    protected function fetch() {
        $res = mysql_fetch_assoc($this->lastquery);
        if (!$res) {
            $this->resultFree();
        }
        return $res;
    }

    //执行SQL没有返回值
    public function exe($sql) {
        $this->optReset(); //查询参数初始化
        $this->debug($sql); //将SQL添加到调试DEBUG
        is_resource($this->link) or $this->connect($this->table);
        $this->lastquery = mysql_query($sql, $this->link);
        if (!$this->lastquery) {
            $this->error();
        }
        return $this->getAffectedRows();
    }

    //发送查询 返回数组
    public function query($sql) {
        $cache_time = $this->cacheTime;
        if ($cache_time > 0) {
            $result = S($sql, FALSE, null, array("dir" => PATH_TEMP_SELECT, "zip" => false));
            if ($result) {
                $this->optReset(); //查询参数初始化
                return $result;
            }
        }
        //SQL发送失败
        if (!$this->exe($sql) || !$this->lastquery)
            return NULL;
        $list = array();
        while (($res = $this->fetch()) != false) {
            $list [] = $res;
        }
        if ($cache_time > 0 && count($list) <= C("CACHE_SELECT_LENGTH")) {
            S($sql, $list, $cache_time, array("dir" => PATH_TEMP_SELECT, "zip" => false));
        }
        return $list ? $list : false;
    }

    //释放结果集
    protected function resultFree() {
        if (isset($this->lastquery)) {
            mysql_free_result($this->lastquery);
        }
        $this->result = null;
    }

    //操作错误
    protected function error() {
        if (mysql_errno()) {
            error(mysql_error() . "\t" . $this->lastSql);
        }
    }

    // 错误代码
    protected function errno() {
        if (mysql_errno()) {
            error(mysql_errno() . "\t" . $this->lastSql);
        }
    }

    // 获得MYSQL版本信息
    public function getVersion() {
        is_resource($this->link) or $this->connect($this->table);
        return preg_replace("/[a-z-]/i", "", mysql_get_server_info());
    }

    //开启事务处理
    public function beginTrans() {
        mysql_query("START AUTOCOMMIT=0");
    }

    //提供一个事务
    public function commit() {
        mysql_query("COMMIT", $this->link);
    }

    //回滚事务
    public function rollback() {
        mysql_query("ROLLBACK", $this->link);
    }

    // 释放连接资源
    public function close() {
        if (is_resource($this->link)) {
            mysql_close($this->link);
            self::$db_link = null;
            $this->link = null;
        }
    }

    //析构函数  释放连接资源
    public function __destruct() {
        $this->close();
    }

}