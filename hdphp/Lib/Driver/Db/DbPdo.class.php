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
 * PDO数据库操作类
 * @package     Db
 * @subpackage  Driver
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
class DbPdo extends Db
{

    static protected $dbLink = null; //是否连接
    public $link = null; //数据库连接
    private $PDOStatement = null; //预准备
    public $affectedRows; //受影响条数

    function getLink()
    {
        if (is_null(self::$dbLink)) {
            $dsn = "mysql:host=" . C("DB_HOST") . ';dbname=' . C("DB_DATABASE");
            $username = C("DB_USER");
            $password = C("DB_PASSWORD");
            try {
                self::$dbLink = new Pdo($dsn, $username, $password);
            } catch (PDOException $e) {
                error($e->getMessage(), false);
            }
            self::setCharts();
        }
        $this->link = self::$dbLink;
        return $this->link;
    }

    /**
     * 设置字符集
     */
    static private function setCharts()
    {
        $character = C("CHARSET");
        $sql = "SET character_set_connection=$character,character_set_results=$character,character_set_client=binary";
        self::$dbLink->query($sql);
    }

    //获得最后插入的ID号
    public function getInsertId()
    {
        return $this->link->lastInsertId();
    }

    //获得受影响的行数
    public function getAffectedRows()
    {
        return $this->affectedRows;
    }

    //执行SQL没有返回值
    public function exe($sql)
    {
        $this->optReset(); //查询参数初始化
        $this->debug($sql); //将SQL添加到调试DEBUG
        //释放结果
        if (!$this->PDOStatement)
            $this->resultFree();
        $this->PDOStatement = $this->link->prepare($sql);
        //预准备失败
        if ($this->PDOStatement === false) {
            $this->error();
        }
        $result = $this->PDOStatement->execute();
        //执行SQL失败
        if ($result === false) {
            $this->error();
            return false;
        } else {
            $this->affectedRows = $this->PDOStatement->rowCount();
            return $this->affectedRows;
        }
    }

    //发送查询 返回数组
    public function query($sql)
    {
        $cache_time = $this->cacheTime;
        if ($cache_time > 0) {
            $result = S($sql, FALSE, null, array("dir" => PATH_TEMP_SELECT, "zip" => false));
            if ($result) {
                $this->optReset(); //查询参数初始化
                return $result;
            }
        }
        //发送SQL
        $this->exe($sql);
        $list = $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);
        //受影响条数
        $this->affectedRows = count($list);
        if ($cache_time > 0 && count($list) <= C("CACHE_SELECT_LENGTH")) {
            S($sql, $list, $cache_time, array("dir" => PATH_TEMP_SELECT, "zip" => false));
        }
        return $list ? $list : false;
    }

    //遍历结果集(根据INSERT_ID)
    protected function fetch()
    {
        $res = $this->lastquery->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            $this->resultFree();
        }
        return $res;
    }

    //释放结果集
    protected function resultFree()
    {
        $this->PDOStatement = NULL;
    }

    //操作错误
    protected function error()
    {
        $info = $this->link->errorInfo();
        if ($info) {
            $error = isset($info[2]) ? $info[2] : $info;
            error("[SQL]" . $error . "\t" . $this->lastSql . " [TIME]" . date("Y-m-d h:i:s") . " [FILE]" . __FILE__ . " [LINE]" . __LINE__);
        }
    }

    // 错误代码
    protected function errno()
    {
        if ($this->link->errorCode()) {
            error($this->link->errorCode() . "\t" . $this->lastSql);
        }
    }

    // 获得MYSQL版本信息
    public function getVersion()
    {
        return $this->link->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    //开启事务处理
    public function beginTrans()
    {
        $this->link->beginTransaction();
    }

    //提供一个事务
    public function commit()
    {
        $this->link->commit();
    }

    //回滚事务
    public function rollback()
    {
        $this->link->rollback();
    }

    // 释放连接资源
    public function close()
    {
        if (is_object($this->link)) {
            $this->link = NULL;
            self::$dbLink = NULL;
        }
    }

    //析构函数  释放连接资源
    public function __destruct()
    {
        $this->close();
    }

}