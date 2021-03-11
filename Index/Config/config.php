<?php
if (!defined("HDPHP_PATH"))exit('No direct script access allowed');
//更多配置请查看hdphp/Config/config.php
$arr = array(

	 /********************************验证码********************************/
    "CODE_FONT"                     => HDPHP_PATH . "Data/Font/font.ttf",       //字体
    "CODE_STR"                      => "123456789abcdefghijklmnpqrstuvwsyz", //验证码种子
    "CODE_WIDTH"                    => 150,         //宽度
    "CODE_HEIGHT"                   => 45,          //高度
    "CODE_BG_COLOR"                 => "#ffffff",   //背景颜色
    "CODE_LEN"                      => 1,           //文字数量
    "CODE_FONT_SIZE"                => 22,          //字体大小
    "CODE_FONT_COLOR"               => "",          //字体颜色

    "COOKIE_TIME" =>time() +3600*24*30,
     /********************************URL设置********************************/
    "HTTPS"                         => FALSE,       //基于https协议
    "URL_REWRITE"                   => 1,           //url重写模式
    "URL_TYPE"                      => 2,           //类型 1:PATHINFO模式 2:普通模式 3:兼容模式
    "PATHINFO_DLI"                  => "/",         //PATHINFO分隔符
    "PATHINFO_VAR"                  => "q",         //兼容模式get变量
    "PATHINFO_HTML"                 => ".html",     //伪静态扩展名
    /********************************url变量********************************/
    "VAR_APP"                       => "a",         //应用变量名，应用组模式有效
    "VAR_CONTROL"                   => "c",         //模块变量
    "VAR_METHOD"                    => "m",         //动作变量


    /********************************文件上传********************************/
     
    "UPLOAD_EXT_SIZE"               => array("jpg" => 5000000, "jpeg" => 5000000, "gif" => 5000000,
                                    "png" => 5000000), //上传类型与大小
    "UPLOAD_PATH"                   => ROOT_PATH . "/upload", //上传路径
    "UPLOAD_IMG_DIR"                => "",       //图片上传目录名
    "UPLOAD_IMG_RESIZE_ON"          => 1,           //上传图片缩放处理,超过以下值系统进行缩放
    "UPLOAD_IMG_MAX_WIDTH"          => 200,     //上传图片超过此值，进行缩放
    "UPLOAD_IMG_MAX_HEIGHT"         => 200,     //上传图片超过此值，进行缩放
    
	
);

return array_merge(include "./Conf/webConfig.php",include "./Conf/dbConfig.php",$arr);
?>