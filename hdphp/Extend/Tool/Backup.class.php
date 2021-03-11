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
 * 数据库备份类
 * @package     tools_class
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
final class Backup
{

    static private $config = array(); //配置项

    function __construct()
    {

    }

    /**
     *
     * @param array $args参数必须为数组，各值说明如下
     * $args=array(
     *  "type"=>"file",//备份方式  file文件大小   row按条数据
     *  "dir"=>"",//备份文件存放目录，不填则使用C("DB_BACKUP")
     *  "url"=>"",//备份成功后跳转到的url
     *  "table"=>array("admin","category")或用字符串指定"admin,category"
     *  "db_name"=>"",//备份的数据库，不填则使用配置项C('DB_DATABASE')
     *  "step_time"=>"",//备份间隔时间，默认1秒
     *  "row"=>300,//每次备份条数，默认200条
     *  "msg"=>"",//配置文件中的备注
     * );
     */
    static public function backup($args = array())
    {
        if (!is_array($args)) {
            throw_exception("backup备份方法的参数必须为数组");
        }
        self::init($args);
        $table = isset($_GET['hdphp_table']) ? $_GET['hdphp_table'] : key(self::$config['table']); //备份表
        $tableInfo = self::$config['table'][$table];
        $fileId = isset($_GET['hdphp_fileid']) ? $_GET['hdphp_fileid'] : 1; //备份文件编号
        $bakFile = self::$config['dir'] . '/' . $table . '_' . $fileId . '.php'; //备份文件
        $db = M($table, true);
        $sqlStr = '';
        $start = ($fileId - 1) * self::$config['row']; //起始条
        $end = $fileId * self::$config['row']; //结束条
        //第1个文件添加建表SQL
        if ($fileId == 1) {
            $dropTableSql = "\$db->exe(\"DROP TABLE IF EXISTS `$table`\");\n";
            $result = $db->query("SHOW CREATE TABLE `$table`");
            $createSql = $result[0]["Create Table"];
            $createTableSql = "\$db->exe(\"$createSql\");\n";
            $sqlStr .= $dropTableSql . $createTableSql;
        }
        for ($i = $start; $i < $end; $i++) {
            $row = $db->limit(array($i, 1))->find();
            if (!$row)
                break;
            $replaceData = $db->db->formatField($row);
            $sqlStr .= "\$db->exe('REPLACE INTO `" . $table . "`(" . implode(',', $replaceData['fields']) . ")" .
                "VALUES (" . implode(',', $replaceData['values']) . ")');\n";
        }
        //**********************写入备份文件
        $sqlStr = "<?php if(!defined('HDPHP_PATH'))exit;\n" . $sqlStr . "\n?>";
        file_put_contents($bakFile, $sqlStr);
        //**********************备份表完成
        if ($i >= $tableInfo['totalrows']) {
            self::$config['table'][$table]['totalfiles'] = $fileId;
            $str = "<?php return " . var_export(self::$config, true) . ";\n?>";
            file_put_contents(self::$config['dir'] . '/Config.php', $str);
            foreach (self::$config['table'] as $tableName => $tableData) {
                if ($tableData['totalfiles'] == 0) {
                    $url = __URL__ . '&hdphp_table=' . $tableName . '&hdphp_bakdir=' . str_replace("/", "@@", self::$config['dir']);
                    self::success("数据表{$tableName}备份开始....", $url);
                }
            }
            self::success("所有表备份已经完成", self::$config['url']);
        } else {
            $url = __URL__ . '&hdphp_fileid=' . (++$fileId) . '&hdphp_table=' . $table . '&hdphp_bakdir=' . urlencode(str_replace("/", "@@", self::$config['dir']));
            self::success("备份表{$table}" . ($start + 1) . "到" . $end . "条记录...", $url);
        }
    }

    //初始化设置
    static private function init($args)
    {
        C("URL_TYPE", 2);
        $dir = isset($_GET['hdphp_bakdir']) ? str_replace("@@", "/", urldecode($_GET['hdphp_bakdir'])) : (isset($args['dir']) && !empty($args['dir']) ? $args['dir'] : C("DB_BACKUP")); //备份目录
        if (!$dir || !dir_create($dir)) {
            throw_exception("备份目录不存在或创建失败");
        }
        if (!isset($args['url']) || empty($args['url'])) {
            throw_exception("必须指定备份或恢复完成后的跳转URL");
        }
        $configFile = $dir . '/Config.php';
        if (is_file($configFile)) {
            self::$config = include $configFile;
        } else {
            $config['type'] = isset($args['type']) && in_array(strtolower($args["type"]), array("row", "file")) ? $args['type'] : "row";
            $config['db_name'] = isset($args['db_name']) ? $args['db_name'] : C("DB_DATABASE"); //备份的数据库
            $config['row'] = isset($args['row']) ? $args['row'] : 200; //每次备份条数
            $config['msg'] = isset($args['msg']) ? $args['msg'] : '后盾网  人人做后盾'; //备注内容
            $backTables = null; //备份的表
            if (isset($args['table'])) {
                if (!is_array($args['table'])) {
                    error("备份表时的参数table必须为数组");
                }
                $backTables = $args['table']; //需要备份的表
            }
            C("DB_DATABASE", $config['db_name']); //设置数据库
            $db = M();
            $databaseInfo = $db->getTableInfo(); //所有表信息
            $tableAll = array_keys($databaseInfo['table']); //库中所有表名
            //没有传参备份所有表
            if (is_null($backTables)) {
                $backTables = $tableAll;
            }
            $tableConfig = array();
            foreach ($backTables as $table) {
                if (in_array($table, $tableAll)) {
                    $tableConfig[$table]['totalfiles'] = 0; //文件总数
                    $tableConfig[$table]['totalrows'] = $databaseInfo['table'][$table]['rows'] * 1; //文件总数
                }
            }
            $config['table'] = $tableConfig;
            file_put_contents($configFile, "<?php if(!defined('HDPHP_PATH'))EXIT;\nreturn " . var_export($config, true) . ";\n?>");
            self::$config = $config;
        }
        self::$config['url'] = U($args['url']);
        self::$config['step_time'] = isset($args['step_time']) ? $args['step_time'] : 1; //备份间隔时间
        self::$config['dir'] = $dir;
    }

    /**
     *
     * @param array $args参数必须为数组，各值说明如下
     * $args=array(
     *  "dir"=>"",//备份文件存放目录，不填则使用C("DB_BACKUP")
     *  "url"=>"",//备份成功后跳转到的url
     * );
     */
    static public function recovery($args)
    {
        if (!is_array($args)) {
            throw_exception("recovery数据还原方法的参数必须为数组");
        }
        if (!isset($args['dir'])) {
            throw_exception("还原数据必须传递dir参数");
        }
        if (!isset($args['url'])) {
            throw_exception("请设置还原完成跳转的url");
        }
        self::init($args);
        $table = isset($_GET['hdphp_table']) ? $_GET['hdphp_table'] : key(self::$config['table']); //还原表
        $tables = array_keys(self::$config['table']); //所有备份数据表
        $totalTable = count($tables); //备份表总数
        $fileTotal = self::$config['table'][$table]['totalfiles']; //文件总数
        $fileId = isset($_GET['hdphp_fileid']) ? $_GET['hdphp_fileid'] : 1; //备份文件编号
        $bakFile = self::$config['dir'] . '/' . $table . '_' . $fileId . '.php'; //备份文件
        $db = M();
        $end = $fileId == $fileTotal ? true : false; //当前表还原结束
        include $bakFile;
        $url = ''; //跳转URL
        if ($end) {
            $id = 1;
            do {
                if ($id == $totalTable) {
                    self::success("数据库还原完毕", U($args['url']));
                } elseif (current($tables) == $table) {
                    $url = __URL__ . '&hdphp_table=' . next($tables) . '&hdphp_bakdir=' . str_replace("/", "@@", self::$config['dir']);
                    self::success("还原文件" . next($tables) . "_1...", $url);
                }
                $id++;
            } while (next($tables));
        } else {
            $url = __URL__ . '&hdphp_table=' . $table . '&hdphp_bakdir=' . str_replace("/", "@@", self::$config['dir']) . '&hdphp_fileid=' . (++$fileId);
        }
        self::success("还原文件" . $table . "_" . (++$fileId) . "...", $url);
    }

    //提示信息
    static private function success($msg, $url)
    {
        $time = self::$config['step_time']; //停止时间
        $stepTime = $time > 30 ? $time : $time * 1000;
        header("Content-type:text/html;charset=utf8");
        echo "<script>
			document.write('$msg'+'......');
			setTimeout(function(){
				location.href='$url';
			},$stepTime);
		</script>";
        exit;
    }

}

?>