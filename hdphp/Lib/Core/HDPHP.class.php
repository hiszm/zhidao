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
//'-----------------------------------------------------------------------------------
final class HDPHP
{
    /**
     * 初始化应用
     */
    static public function init()
    {
        //解析URL
        if (IS_GROUP) {
            //应用组配置
            if (is_file(COMMON_PATH . 'Config/config.php')) {
                C(require(COMMON_PATH . 'Config/config.php'));
            }
            //解析URL与路由
            Route::group();
        }
        //应用
        define("APP", ucfirst(IS_GROUP ? $_GET[C('VAR_APP')] : basename(substr(APP_PATH, 0, -1))));
        //应用目录
        IS_GROUP and define("APP_PATH", GROUP_PATH . 'App/' . APP . '/');
        //常量
        defined("CONTROL_PATH") or define("CONTROL_PATH", APP_PATH . 'Control/');
        defined("MODEL_PATH") or define("MODEL_PATH", APP_PATH . 'Model/');
        defined("CONFIG_PATH") or define("CONFIG_PATH", APP_PATH . 'Config/');
        defined("EXTEND_PATH") or define("EXTEND_PATH", APP_PATH . 'Extend/');
        defined("EVENT_PATH") or define("EVENT_PATH", EXTEND_PATH . 'Event/');
        defined("LANGUAGE_PATH") or define("LANGUAGE_PATH", EXTEND_PATH . 'Language/');
        defined("TAG_PATH") or define("TAG_PATH", EXTEND_PATH . 'Tag/');
        defined("LIB_PATH") or define("LIB_PATH", EXTEND_PATH . 'Lib/');
        defined("COMPILE_PATH") or define("COMPILE_PATH", TEMP_PATH . (IS_GROUP ? APP . '/Compile/' : 'Compile/'));
        defined("CACHE_PATH") or define("CACHE_PATH", TEMP_PATH . (IS_GROUP ? APP . '/Cache/' : 'Cache/'));
        defined("TABLE_PATH") or define("TABLE_PATH", TEMP_PATH . (IS_GROUP ? APP . '/Table/' : 'Table/'));
        defined("LOG_PATH") or define("LOG_PATH", TEMP_PATH . 'Log/');
        //应用配置
        $app_config = CONFIG_PATH . 'config.php';
        if (is_file($app_config)) C(require($app_config));
        //模板目录
        $tpl = rtrim(C("TPL_DIR"), '/');
        $tpl_style = rtrim(C("TPL_STYLE"), '/');
        define("TPL_PATH", (strstr($tpl, '/') ? $tpl . '/' : APP_PATH . $tpl . '/') . ($tpl_style ? $tpl_style . '/' : $tpl_style));
        define("PUBLIC_PATH", TPL_PATH . 'Public/');
        //应用url解析并创建常量
        Route::app();
        //=========================环境配置
        @ini_set('memory_limit', '128M');
        @ini_set("register_globals", "off");
        @ini_set('magic_quotes_runtime', 0);
        //当前时间
        define('NOW', $_SERVER['REQUEST_TIME']);
        //微秒时间
        define("NOW_MICROTIME", microtime(true));
        //请求方式
        define("MAGIC_QUOTES_GPC", @get_magic_quotes_gpc() ? true : false);
        define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
        define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
        define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
        define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
        define("IS_AJAX", ajax_request());
        define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);

        //注册自动载入函数
        spl_autoload_register(array(__CLASS__, "autoload"));
        //设置错误处理函数
        set_error_handler(array(__CLASS__, "error"), E_ALL);
        //设置异常
        set_exception_handler(array(__CLASS__, "exception"));
        //session处理
        O("Session" . ucwords(C("SESSION_ENGINE")), "run");
        !ini_get("session.auto_start") and C("SESSION_AUTO") and session_start();
        //加载语言包
        is_file(CONFIG_PATH . C('LANGUAGE')) and L(require CONFIG_PATH . C('LANGUAGE'));
        //加载行为配置
        C("CORE_EVENT", require HDPHP_CONFIG_PATH . "event.php");
        IS_GROUP and is_file(COMMON_CONFIG_PATH . 'event.php') and C("GROUP_EVENT", require COMMON_CONFIG_PATH . 'event.php');
        is_file(CONFIG_PATH . 'event.php') and C("APP_EVENT", require CONFIG_PATH . 'event.php');
        //别名导入
        IS_GROUP and is_file(COMMON_LIB_PATH . 'Alias.php') and alias_import(COMMON_LIB_PATH . 'Alias.php');
        is_file(LIB_PATH . 'Alias.php') and alias_import(LIB_PATH . 'Alias.php');
    }

    /**
     * 自动载入函数
     * @param string $className 类名
     * @access private
     * @return void
     */
    static private function autoload($className)
    {
        $class = ucfirst($className) . '.class.php'; //类文件
        if (require_array(array(
            EVENT_PATH . $class,
            LIB_PATH . $class,
            TAG_PATH . $class,
            HDPHP_CORE_PATH . $class,
            HDPHP_EXTEND_PATH . $class,
            HDPHP_EXTEND_PATH . '/Tool/' . $class
        ))
        ) {
            return true;
        } elseif (substr($className, -5) == "Model") {
            if (require_array(array(
                MODEL_PATH . $class,
                HDPHP_DRIVER_PATH . 'Model/' . $class
            ))
            ) return;
        } elseif (substr($className, -7) == "Control") {
            if (require_array(array(
                CONTROL_PATH . $class,
                HDPHP_CORE_PATH . $class
            ))
            ) return;
        } elseif (substr($className, 0, 2) == 'Db') {
            if (require_array(array(
                HDPHP_DRIVER_PATH . 'Db/' . $class
            ))
            ) return;
        } elseif (substr($className, 0, 5) == "Cache") {
            if (require_array(array(
                HDPHP_DRIVER_PATH . 'Cache/' . $class,
            ))
            ) return;
        } elseif (substr($className, 0, 7) == "Session") {
            if (require_array(array(
                HDPHP_DRIVER_PATH . 'Session/' . $class
            ))
            ) return;
        } elseif (substr($className, -5) == "Event") {
            if (require_array(array(
                EVENT_PATH . $class
            ))
            ) return;
        } elseif (alias_import($className)) {
            return;
        }
        $msg = "Class {$class} not found";
        Log::write($msg);
        error($msg);
    }

    /**
     * 自定义异常理
     * @param $e
     */
    static public function exception($e)
    {
        $error = array();
        $error['message'] = $e->getMessage();
        $trace = $e->getTrace();
        if ($trace[0]['function'] == 'throw_exception') {
            $error['file'] = $trace[0]['file'];
            $error['line'] = $trace[0]['line'];
        } else {
            $error['file'] = $e->getFile();
            $error['line'] = $e->getLine();
        }
        error($error);
    }

    /**
     * 错误处理
     */
    static public function error($errno, $error, $file, $line)
    {
        $errorType = substr(FriendlyErrorType($errno), 2);
        $msg = "[$errorType]" . $error . ' [TIME]' . date("Y-m-d h:i:s") . ' [FILE]' . $file . ' [LINE]' . $line ;
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_USER_ERROR:
                error($msg);
                break;
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                Log::set($msg, $errno);
                if (DEBUG && C("SHOW_WARNING"))
                    include HDPHP_TPL_PATH . 'notice.html';
                break;
        }
    }
}

?>
