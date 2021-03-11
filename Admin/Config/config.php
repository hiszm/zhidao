<?php
if (!defined("HDPHP_PATH"))exit('No direct script access allowed');
//更多配置请查看hdphp/Config/config.php
$arr =  array(
    /********************************验证码********************************/
    "CODE_FONT"                     => HDPHP_PATH . "Data/Font/font.ttf",       //字体
    "CODE_STR"                      => "123456789abcdefghijklmnpqrstuvwsyz", //验证码种子
    "CODE_WIDTH"                    => 90,         //宽度
    "CODE_HEIGHT"                   => 30,          //高度
    "CODE_BG_COLOR"                 => "#ffffff",   //背景颜色
    "CODE_LEN"                      => 1,           //文字数量
    "CODE_FONT_SIZE"                => 22,          //字体大小
    "CODE_FONT_COLOR"               => "",          //字体颜色
    /********************************URL设置********************************/
    "HTTPS"                         => FALSE,       //基于https协议
    "URL_REWRITE"                   => 1,           //url重写模式
    "URL_TYPE"                      => 2,           //类型 1:PATHINFO模式 2:普通模式 3:兼容模式
    "PATHINFO_DLI"                  => "/",         //PATHINFO分隔符
    "PATHINFO_VAR"                  => "q",         //兼容模式get变量
    "PATHINFO_HTML"                 => ".html",     //伪静态扩展名
);
return array_merge(include "./Conf/webConfig.php", include "./Conf/dbConfig.php", $arr);
?>