<?php

require_cache(HDPHP_DRIVER_PATH . 'Db/Db.class.php');
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
 * mysqli数据库驱动
 * @package     Db
 * @subpackage  Driver
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
class DbMysqli extends Db
{

    static protected $dbLink = null; //是否连接
    public $link = null; //数据库连接

    function getLink()
    {
        if (is_null(self::$dbLink)) {
            self::$dbLink = new mysqli(C("DB_HOST"), C("DB_USER"), C("DB_PASSWORD"), C("DB_DATABASE"), intval(C("DB_PORT")));
            if (mysqli_connect_errno()) {
                error(mysqli_connect_error() . L("mysqlidriver_connectDb"), false); //数据库连接出错了请检查配置文件中的参数
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
        return $this->link->insert_id;
    }

    //获得受影响的行数
    public function getAffectedRows()
    {
        return $this->link->affected_rows;
    }

    //遍历结果集(根据INSERT_ID)
    protected function fetch()
    {
        $res = $this->lastquery->fetch_assoc();
        if (!$res) {
            $this->resultFree();
        }
        return $res;
    }

    //执行SQL没有返回值
    public function exe($sql)
    {
        if (!trim($sql)) return false;
        $this->optReset(); //查询参数初始化
        is_object($this->link) or $this->connect($this->table);
        $this->debug($sql); //将SQL添加到调试DEBUG
        $this->lastquery = $this->link->query($sql);
        if (!$this->lastquery) {
            $this->error();
        }
        return $this->getAffectedRows();
    }

    //发送查询 返回数组
    public function query($sql)
    {
        $cache_time = is_null($this->cacheTime) ? intval(C("CACHE_SELECT_TIME")) : $this->cacheTime;
        $cacheName = $sql . APP . CONTROL . METHOD;
        if ($cache_time >= 0) {
            $result = S($cacheName, FALSE, null, array("Driver" => "file", "dir" => CACHE_PATH, "zip" => false));
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
        if ($cache_time >= 0 && count($list) <= C("CACHE_SELECT_LENGTH")) {
            S($cacheName, $list, $cache_time, array("Driver" => "file", "dir" => CACHE_PATH, "zip" => false));
        }
        return $list ? $list : NULL;
    }

    //释放结果集
    protected function resultFree()
    {
        if (isset($this->lastquery)) {
            $this->lastquery->close();
        }
    }

    //操作错误
    protected function error()
    {
        if ($this->link->error) {
            error($this->link->error . "\t" . $this->lastSql);
        }
    }

    // 错误代码
    protected function errno()
    {
        if ($this->link->errno) {
            error(intval($this->link->errno));
        }
    }

    // 获得MYSQL版本信息
    public function getVersion()
    {
        is_object($this->link) or $this->connect($this->table);
        return preg_replace("/[a-z-]/i", "", $this->link->server_info);
    }

    //自动提交模式true开启false关闭
    public function beginTrans()
    {
        $this->link->autocommit(FALSE);
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
        if (is_object(self::$dbLink)) {
            self::$dbLink->close();
            self::$dbLink = null;
            $this->link = null;
        }
    }

    //析构函数  释放连接资源
    public function __destruct()
    {
        $this->close();
    }

}