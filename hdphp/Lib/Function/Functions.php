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
 * 系统核心函数库
 * @category    HDPHP
 * @package     Lib
 * @subpackage  core
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
/**
 * 加载核心模型
 * @param String $tableName 表名
 * @param Boolean $full 是否为全表名
 * @return Object 返回模型对象
 */
function M($tableName = null, $full = null)
{
    return new Model($tableName, $full);
}

/**
 * @param $model 扩展模型名称
 * @param $table 表名
 * @return Model
 */
function K($model, $table = false)
{
    //获得模型文件
    $info = explode('/', trim($model, '/'));
    $class = "";
    $path = "";
    switch (count($info)) {
        case 1:
            $class = $info[0] . C('MODEL_FIX');
            $path = APP_PATH . 'Model/';
            break;
        case 2:
            $class = $info[1] . C('MODEL_FIX');
            $path = APP_PATH . '../' . $info[0] . '/Model/';
            break;
        default:
            $class = basename($model) . C('MODEL_FIX');
            $path = dirname($model);
    }
    $class = ucfirst($class);
    if (!import($class, $path)) {
        //还没有定义模型文件
        error(L("functions_k_is_file") . $path . $class . '.class.php', false);
    }
    if (!class_exists($class, false)) {
        error(L("functions_k_error") . $class, false); //模型类定义有误
    }
    $table = $table === false ? substr(strtolower($class), 0, -strlen(C("MODEL_FIX"))) : $table;
    return new $class($table);
}

/**g
 * @param String $tableName 表名
 * @param Boolean $full 是否为全表
 * @return relationModel
 */
function R($tableName = null, $full = null)
{
    return new relationModel($tableName, $full);
}

/**
 * 获得视图模型
 * @param null $tableName 表名
 * @param null $full 带前缀
 * @return ViewModel
 */
function V($tableName = null, $full = null)
{
    return new ViewModel($tableName, $full);
}


/**
 * 快速缓存 以文件形式缓存
 * @param String $name 缓存KEY
 * @param bool $value 删除缓存
 * @param string $path 缓存目录
 * @return bool
 */
function F($name, $value = false, $path = CACHE_PATH)
{
    $_cache = array();
    $name = md5($name);
    $cacheFile = rtrim($path, '/') . '/' . $name . '.php';
    if (is_null($value)) {
        if (is_file($cacheFile)) {
            unlink($cacheFile);
            unset($_cache[$name]);
        }
        return true;
    }
    if ($value === false) {
        if (isset($_cache[$name]))
            return $_cache[$name];
        return is_file($cacheFile) ? include $cacheFile : null;
    }
    $data = "<?php if(!defined('HDPHP_PATH'))exit;\nreturn " . compress(var_export($value, true)) . ";\n?>";
    is_dir($path) || dir_create($path);
    if (!file_put_contents($cacheFile, $data)) {
        return false;
    }
    $_cache[$name] = $data;
    return true;
}


/**
 * 缓存处理
 * @param string $name 缓存名称
 * @param bool $value 缓存内容
 * @param null $expire 缓存时间
 * @param array $options 选项
 * <code>
 * array("Driver"=>"file","dir"=>"Cache","Driver"=>"memcache")
 * </code>
 * @return bool
 */
function S($name, $value = false, $expire = null, $options = array())
{
    static $_data = array();
    $cacheObj = Cache::init($options);
    if (is_null($value)) {
        return $cacheObj->del($name);
    }
    $driver = isset($options['Driver']) ? $options['Driver'] : '';
    $key = $name . $driver;
    if ($value === false) {
        if (isset($_data[$key])) {
            Debug::$cache['read_s']++;
            return $_data[$key];
        } else {
            return $cacheObj->get($name, $expire);
        }
    }
    $cacheObj->set($name, $value, $expire);
    $_data[$key] = $value;
    return true;
}

/**
 * 加载控制器
 * @param $path 控制器文件
 * @return array
 */
//function get_control_file($path)
//{
//    $pathArr = explode('/', trim($path, '/'));
//    switch (count($pathArr)) {
//        case 1:
//            //当前应用
//            $base = APP_PATH . $pathArr[0];
//            break;
//        case 2:
//            //其它应用
//            $base = APP_PATH . '../' . $pathArr[0] . '/Control/' . $pathArr[1];
//            break;
//    }
//    $name = basename($base);
//    import($name . C('Control'), $base);
//    if (class_exists($name))
//        return array($controlFile, array_pop($pathArr));
//}

/**
 * 实例化控制器对象
 * @param string $control
 * @return Object
 */
function control($control)
{
    $pathArr = explode('/', trim($control, '/'));
    switch (count($pathArr)) {
        case 1:
            //当前应用
            $base = APP_PATH . 'Control/' . $pathArr[0];
            break;
        case 2:
            //其它应用
            $base = APP_PATH . '../' . $pathArr[0] . '/Control/' . $pathArr[1];
            break;
    }

    //控制器名
    $class = basename($base) . C('CONTROL_FIX');
    if (require_cache($base . C('CONTROL_FIX') . '.class.php')) {
        if (class_exists($class))
            return new $class;
    }
    return false;
}


/**
 * SESSION
 * @param string $name 名称
 * @param string $value 值
 * @return null
 */
function session($name = false, $value = '')
{
    static $_start = false;
    if ($name === false) return $_SESSION;
    if ($_start === false) {
        $_start = true;
        session_id() || session_start();
    }
    //清空SESSION
    if (is_null($name)) {
        $_SESSION = array();
        session_unset();
        session_destroy();
    } elseif (is_null($value)) { //删除SESSION
        unset($_SESSION[$name]);
    } elseif (empty($value)) { //获得SESSION赋值
        switch (strtolower($name)) {
            case "[parse]": //停止SESSION
                session_write_close();
                break;
        }
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    } else { //赋值SESSION
        $_SESSION[$name] = $value;
    }
}

/**
 * 生成对象 || 执行方法
 * @param $class 类名
 * @param string $method 方法
 * @param array $args 参数
 * @return mixed
 */
function O($class, $method = '', $args = array())
{
    static $result = array();
    $name = empty($args) ? $class . $method : $class . $method . md5_d($args);
    if (!isset($result [$name])) {
        $class = new $class ();
        if (!empty($method) && method_exists($class, $method)) {
            if (!empty($args)) {
                $result [$name] = call_user_func_array(array(&$class, $method), $args);
            } else {
                $result [$name] = $class->$method();
            }
        } else {
            $result [$name] = $class;
        }
    }
    return $result [$name];
}

/**
 * 载入或设置配置顶
 * @param string $name 配置名
 * @param string $value 配置值
 * @return bool|null
 */
function C($name = null, $value = null)
{
    static $config = array();
    if (is_null($name)) {
        return $config;
    }
    if (is_array($value)) {
        $value = array_change_key_case_d($value);
    }
    if (is_string($name)) {
        $name = strtolower($name);
        if (!strstr($name, '.')) {
            if (is_null($value)) {
                if (isset($config[$name]) && !is_array($config[$name])) {
                    $config[$name] = trim($config[$name]);
                }
                return isset($config [$name]) ? $config [$name] : null;
            }
            $config [$name] = isset($config[$name]) && is_array($config[$name]) && is_array($value) ? array_merge($config[$name], $value) : $value;
            return $config[$name];
        }
//二维数组
        $name = array_change_key_case_d(explode(".", $name), 0);
        if (is_null($value)) {
            return isset($config [$name[0]] [$name[1]]) ? $config [$name[0]][$name[1]] : null;
        }
        $config [$name[0]] [$name[1]] = $value;
    }
    if (is_array($name)) {
        $config = array_merge($config, array_change_key_case_d($name, 0));
        return true;
    }
}

//加载语言处理
function L($name = null, $value = null)
{
    static $languge = array();
    if (is_null($name)) {
        return $languge;
    }
    if (is_string($name)) {
        $name = strtolower($name);
        if (!strstr($name, '.')) {
            if (is_null($value))
                return isset($languge [$name]) ? $languge [$name] : null;
            $languge [$name] = $value;
            return $languge[$name];
        }
//二维数组
        $name = array_change_key_case_d(explode(".", $name), 0);
        if (is_null($value)) {
            return isset($languge [$name[0]] [$name[1]]) ? $languge [$name[0]][$name[1]] : null;
        }
        $languge [$name[0]] [$name[1]] = $value;
    }
    if (is_array($name)) {
        $languge = array_merge($languge, array_change_key_case_d($name));
        return true;
    }
}

/**
 * 执行事件中的所有处理程序
 * @param $name 事件名称
 * @param void $param 参数
 * return void
 */
function event($name, $param = null)
{
    //框架核心事件
    $core = C("CORE_EVENT." . $name);
    //应用组事件
    $group = C("GROUP_EVENT." . $name);
    //应用事件
    $event = C("APP_EVENT." . $name);
    if (is_array($group)) {
        if ($core) {
            $group = array_merge($core, $group);
        }
    } else {
        $group = $core;
    }
    if (is_array($group)) {
        if ($event) {
            $event = array_merge($group, $event);
        }
    }
    if (is_array($event)) {
        foreach ($event as $e) {
            E($e, $param);
        }
    }

}

/**
 * 执行单一事件处理程序
 * @param string $name 事件名称
 * @param null $params 事件参数
 */
function E($name, &$params = null)
{
    $class = $name . "Event";
    $event = new $class;
    $event->run($params);
}

/**
 * 打印输出数据
 * @param void $var
 */
function show($var)
{
    // echo "<pre>" . htmlspecialchars(print_r($var, true), ENT_QUOTES) . "</pre>";
    echo "<pre>" . print_r($var, true) . "</pre>";
}

/**
 * 打印输出数据|show的别名
 * @param void $var
 */
function p($var)
{
    show($var);
}

/**
 * 打印输出数据|show的别名
 * @param void $var
 */
function dump($var)
{
    show($var);
}

/**
 * 跳转网址
 * @param string $url 跳转urlg
 * @param int $time 跳转时间
 * @param string $msg
 */
function go($url, $time = 0, $msg = '')
{
    $url = U($url);
    if (!headers_sent()) {
        $time == 0 ? header("Location:" . $url) : header("refresh:{$time};url={$url}");
        exit($msg);
    } else {
        echo "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time)
            exit($msg);
    }
}


/**
 * 获得客户端IP地址
 * @param int $type 类型
 * @return int
 */
function ip_get_client($type = 0)
{
    $ip = ''; //保存客户端IP地址
    if (isset($_SERVER)) {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else {
            $ip = getenv("REMOTE_ADDR");
        }
    }
    $long = ip2long($ip);
    $clientIp = $long ? array($ip, $long) : array("0.0.0.0", 0);
    return $clientIp[$type];
}

/**
 * 是否为AJAX提交
 * @return boolean
 */
function ajax_request()
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        return true;
    return false;
}

/**
 * 对数组或字符串进行转义处理，数据可以是字符串或数组及对象
 * @param void $data
 * @return type
 */
function addslashes_d($data)
{
    if (is_string($data)) {
        return addslashes($data);
    }
    if (is_numeric($data)) {
        return $data;
    }
    if (is_array($data)) {
        $var = array();
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $var[$k] = addslashes_d($v);
                continue;
            } else {
                $var[$k] = addslashes($v);
            }
        }
        return $var;
    }
}

/**
 * 去除转义
 * @param type $data
 * @return type
 */
function stripslashes_d($data)
{
    if (empty($data)) {
        return $data;
    } elseif (is_string($data)) {
        return stripslashes($data);
    } elseif (is_array($data)) {
        $var = array();
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $var[$k] = stripslashes_d($v);
                continue;
            } else {
                $var[$k] = stripslashes($v);
            }
        }
        return $var;
    }
}

/**
 * 将数组转为字符串表示形式
 * @param array $array 数组
 * @param int $level 等级不要传参数
 * @return string
 */
function array_to_String($array, $level = 0)
{
    if (!is_array($array)) {
        return "'" . $array . "'";
    }
    $space = ''; //空白
    for ($i = 0; $i <= $level; $i++) {
        $space .= "\t";
    }
    $arr = "Array\n$space(\n";
    $c = $space;
    foreach ($array as $k => $v) {
        $k = is_string($k) ? '\'' . addcslashes($k, '\'\\') . '\'' : $k;
        $v = !is_array($v) && (!preg_match("/^\-?[1-9]\d*$/", $v) || strlen($v) > 12) ?
            '\'' . addcslashes($v, '\'\\') . '\'' : $v;
        if (is_array($v)) {
            $arr .= "$c$k=>" . array_to_String($v, $level + 1);
        } else {
            $arr .= "$c$k=>$v";
        }
        $c = ",\n$space";
    }
    $arr .= "\n$space)";
    return $arr;
}

/**
 *  对变量进行 JSON 编码
 */
if (!function_exists('json_encode')) {

    function json_encode($value)
    {
        $json = new json();
        return $json->encode($value);
    }

}
/**
 *  对JSON格式的字符串进行编码
 */
if (!function_exists('json_decode')) {

    function json_decode($json_value, $bool = false)
    {
        $json = new json();
        return $json->decode($json_value, $bool);
    }

}

/**
 * 手机号码查询
 * */
function mobile_area($mobile)
{
    //导入类库
    require_cache(HDPHP_EXTEND_PATH . "Org/Mobile/Mobile.class.php");
    return Mobile::area($mobile);
}


/**
 * 根据类型获得图像扩展名
 */
if (!function_exists('image_type_to_extension')) {

    function image_type_to_extension($type, $dot = true)
    {
        $e = array(1 => 'gif', 'jpeg', 'png', 'swf', 'psd', 'bmp',
            'tiff', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc',
            'aiff', 'wbmp', 'xbm');
        $type = (int)$type;
        return ($dot ? '.' : '') . $e[$type];
    }

}

/**
 * 获得随机字符串
 * @param int $len 长度
 * @return string
 */
function rand_str($len = 6)
{
    $data = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $str = '';
    while (strlen($str) < $len)
        $str .= substr($data, mt_rand(0, strlen($data) - 1), 1);
    return $str;
}


/**
 * 加密方法
 * @param $data 加密字符串
 * @param null $key 密钥
 * @return mixed|string
 */
function encrypt($data, $key = null)
{
    return encry::encrypt($data, $key);
}

/**
 * 解密方法
 * @param string $data 解密字符串
 * @param null $key 密钥
 * @return mixed
 */
function decrypt($data, $key = null)
{
    return encry::decrypt($data, $key);
}

/**
 * 数据安全处理
 * @param $data 要处理的数据
 * @param null $func 安全的函数
 * @return array|string
 */
function data_format($data, $func = null)
{
    $functions = is_null($func) ? array("htmlspecialchars", "addslashes")
        : (is_array($func) ? $func : preg_split("/\s*,\s*/", $func));
    foreach ($functions as $_func) {
        if (is_string($data)) { //字符串数据
            $_func($data);
        } else if (is_array($data)) { //数组数据
            foreach ($data as $k => $d) {
                $data[$k] = is_array($d) ? data_format($d, $functions) : $_func($d);
            }
        }
    }
    return $data;
}


/**
 * 替换标签类$attr参数的常量与变量
 * @param string $attr 属性名
 * @param int $type 转换类型  1:PHP输出   2:变量值
 * @return array
 */
//function tpl_format_attr($attr, $type = 1)
//{
//    if (!is_array($attr))
//        $attr = array($attr);
//    $userConsts = get_defined_constants(true);
//    $const = array();
//    foreach ($userConsts['user'] as $k => $v) { //获得用户定义常量
//        if (!strstr($k, '__'))
//            continue;
//        $const[$k] = $v;
//    }
////替换变量
//    $vars = hdView::$vars;
//    foreach ($attr as $k => $at) {
//        switch ($type) {
//            case 1:
//                $attr[$k] = preg_replace('/\$\w+\[.*\](?!=\[)|\$\w+(?!=[a-z])/', '<?php echo \0;? >', $attr[$k]);
//                break;
//            case 2:
//                break;
//        }
////替换常量
//        foreach ($const as $constName => $constValue) {
//            $attr[$k] = str_replace($constName, $constValue, $attr[$k]);
//        }
//    }
//    return $attr;
//}

/**
 * 获得变量值
 * @param string $varName 变量名
 * @param mixed $value 值
 * @return mixed
 */
function _default($varName, $value = "")
{
    return isset($varName) ? $varName : $value;
}


/**
 * 请求方式
 * @param string $method 类型
 * @param string $varName 变量名
 * @param bool $html 实体化
 * @return mixed
 */
function _request($method, $varName = null, $html = true)
{
    $method = strtolower($method);
    switch ($method) {
        case 'ispost' :
        case 'isget' :
        case 'ishead' :
        case 'isdelete' :
        case 'isput' :
            return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method, 2));
        case 'get' :
            $data = & $_GET;
            break;
        case 'post' :
            $data = & $_POST;
            break;
        case 'request' :
            $data = & $_REQUEST;
            break;
        case 'Session' :
            $data = & $_SESSION;
            break;
        case 'cookie' :
            $data = & $_COOKIE;
            break;
        case 'server' :
            $data = & $_SERVER;
            break;
        case 'globals' :
            $data = & $GLOBALS;
            break;
        default:
            throw_exception('abc');
    }
//获得所有数据
    if (is_null($varName))
        return $data;
    if (isset($data[$varName]) && $html) {
        $data[$varName] = htmlspecialchars($data[$varName]);
    }
    return isset($data[$varName]) ? $data[$varName] : null;
}

/**
 * 404错误
 * @param string $msg 提示信息
 * @param string $filePath 404模板文件
 */
function _404($msg = "", $filePath = "")
{
    DEBUG && error($msg);
    Log::write($msg); //写入日志
    if (empty($filePath) && C("404_TPL")) {
        $filePath = C("404_TPL");
    }
    //文件不可操作
    if (!is_file($filePath) || !is_readable($filePath)) {
        $filePath = HDPHP_TPL_PATH . '/404.html';
    }
    set_http_state(404);
    include $filePath;
    exit;
}

/**
 * HTTP状态信息设置
 * @param Number $code 状态码
 */
function set_http_state($code)
{
    $state = array(
        200 => 'OK', // Success 2xx
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',
        // Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );
    if (isset($state[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $state[$code]);
        header('Status:' . $code . ' ' . $state[$code]); //FastCGI模式
    }
}

/**
 * 是否为SSL协议
 * @return boolean
 */
function is_ssl()
{
    if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}

/**
 * 用户定义常量
 * @param bool $view 是否显示
 * @param bool $tplConst 是否只获取__WEB__这样的常量
 * @return array
 */
function print_const($view = true, $tplConst = false)
{
    $define = get_defined_constants(true);
    $const = $define['user'];
    if ($tplConst) {
        $const = array();
        foreach ($define['user'] as $k => $d) {
            if (preg_match('/^__/', $k)) {
                $const[$k] = $d;
            }
        }
    }
    if ($view) {
        p($const);
    } else {
        return $const;
    }
}

/**
 * 获得几天前，几小时前，几月前
 * @param int $time 时间戳
 * @param array $unit 时间单位
 * @return bool|string
 */
function date_before($time, $unit = null)
{
    if (!is_int($time)) return false;
    $unit = is_null($unit) ? array("年", "月", "星期", "日", "小时", "分钟", "秒") : $unit;
    switch (true) {
        case $time < (NOW - 31536000):
            return floor((NOW - $time) / 31536000) . $unit[0];
        case $time < (NOW - 2592000):
            return floor((NOW - $time) / 2592000) . $unit[1];
        case $time < (NOW - 604800):
            return floor((NOW - $time) / 604800) . $unit[2];
        case $time < (NOW - 86400):
            return floor((NOW - $time) / 86400) . $unit[3];
        case $time < (NOW - 3600):
            return floor((NOW - $time) / 3600) . $unit[4];
        case $time < (NOW - 60):
            return floor((NOW - $time) / 60) . $unit[5];
        default:
            return floor(NOW - $time) . $unit[6];

    }
}


