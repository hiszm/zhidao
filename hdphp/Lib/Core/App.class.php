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
final class App
{
    /**
     * 运行应用
     * @access public
     * @reutrn mixed
     */
    public static function run()
    {
        event("APP_START");
        //Debug Start
        DEBUG and Debug::start("APP_START");
        self::init();
        self::start();
        //Debug End
        if (DEBUG) {
            if ((!C("DEBUG_AJAX") && IS_AJAX) || !C("DEBUG_SHOW")) ; else
                Debug::show("APP_START", "APP_END");
        }
        //日志记录
        Log::save();
        event("APP_END");
    }

    /**
     * 运行应用
     * @access private
     */
    private static function start()
    {
        //控制器实例
        $control = control(CONTROL);
        //控制器不存在
        if (!$control) {
            //空控制器
            $control = Control("Empty");
            if (!$control) {
                _404('模块' . CONTROL . '不存在');
            }
        }
        //执行动作
        try {
            $method = new ReflectionMethod($control, METHOD);
            if ($method->isPublic()) {
                $method->invoke($control);
            } else {
                throw new ReflectionException;
            }
        } catch (ReflectionException $e) {
            $method = new ReflectionMethod($control, '__call');
            $method->invokeArgs($control, array(METHOD, ''));
        }
    }

    /**
     * 初始化
     * @access private
     */
    private static function init()
    {
        //设置字符集
        define("CHARSET", preg_match('@utf8@i', C("CHARSET")) ? "UTF-8" : C("CHARSET"));
        define("CHARSET_DB", str_replace("-", "", C("CHARSET")));
        //设置时区
        date_default_timezone_set(C("default_time_zone"));
    }
}

?>