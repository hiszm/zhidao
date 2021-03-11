<?php
if (!defined("HDPHP_PATH"))exit('No direct script access allowed');
//基本配置文件
return array(
    /********************************基本参数********************************/
    "CHARSET"                       => "utf8",      //字符集
    "DEFAULT_TIME_ZONE"             => "PRC",       //时区
    "HTML_PATH"                     => "h",         //静态HTML保存目录
    "DEBUG_SHOW"                    => 1,           //为TRUE时显示DEBUG信息否则显示按钮
//    "DEBUG"                         => 0,         //调试模式 已弃用
//    "DEBUG_AJAX"                    => 0,           //AJAX显示调试信息  已弃用
    "LANGUAGE"                      => "",          //语言包
    "AUTH_KEY"                      => "houdunwang",//验证key
    "CHECK_FILE_CASE"               => 0,           //windows区分大小写
    "AUTO_LOAD_FILE"                => "",          //自动加载应用Lib目录或应用组Common/Lib目录下的文件
    "404_TPL"                       => HDPHP_TPL_PATH . '404.html', //404模板文件
    /********************************数据库********************************/
    "DB_DRIVER"                     => "mysqli",    //数据库驱动
    "DB_HOST"                       => "127.0.0.1", //数据库连接主机  如127.0.0.1
    "DB_PORT"                       => 3306,        //数据库连接端口
    "DB_USER"                       => "root",      //数据库用户名
    "DB_PASSWORD"                   => "",          //数据库密码
    "DB_DATABASE"                   => "",          //数据库名称
    "DB_PREFIX"                     => "",          //表前缀
    "DB_FIELD_CACHE"                => 1,           //字段缓存
    "DB_BACKUP"                     => ROOT_PATH . "backup/".time(), //数据库备份目录
    /********************************表单TOKEN令牌********************************/
    "TOKEN_ON"                      => 0,           //令牌状态
    "TOKEN_NAME"                    => "__TOKEN__", //令牌的表单name
    /********************************系统调试********************************/
    "ERROR_MESSAGE"                 => "出错了！开启DEBUG或查看Log文件",     //关闭DEBUG时的错误内容
    "ERROR_TPL"                     => HDPHP_TPL_PATH . 'halt.html', //错误信息模板
    "DEBUG_MENU"                    => 1,            //显示debug菜单
    "SHOW_WARNING"                  => 0,           //显示NOTICE或WARNING错误
    "SHOW_SYSTEM"                   => 1,           //系统信息
    "SHOW_CACHE"                    => 1,           //缓存监控,必须将CACHE_SAVE配置开启才有效
    "SHOW_INCLUDE"                  => 1,           //文件列表
    "SHOW_SQL"                      => 1,           //SQL语句
    "SHOW_TPL_COMPILE"              => 1,           //模板编译文件
    /********************************LOG日志处理********************************/
    "LOG_SAVE"                      => 1,           //错写入日志文件
    "LOG_KEY"                       => "houdunwang.com", //日志文件加密KEY
    "LOG_SIZE"                      => 2000000,     //日志文件大小
    "LOG_TYPE"                      => array(), //例array("notice","warning")
    /********************************SESSION********************************/
    "SESSION_AUTO"                  => 1,           //自动开启SESSION
    "SESSION_NAME"                  => "hdsid",     //session_name
    "SESSION_ENGINE"                => "file",      //引擎:file,mysql,memcache
    "SESSION_SAVE_PATH"             => "",          //以文件处理时的位置
    "SESSION_LIFETIME"              => 1440,        //SESSION过期时间
    "SESSION_TABLE_NAME"            => "session",   //SESSION的表名
    "SESSION_GC_DIVISOR"            => 10,          //SESSION清理频率,数字越小清理越频繁
    "SESSION_MEMCACHE"              => array(       //Memcache配置,支持集群
                "host" => "127.0.0.1",  //主机
                "port" => 11211         //端口
    ),
    "SESSION_REDIS"                 => array(       //Redis配置,支持集群
        "host" => "127.0.0.1",          //主机
        "port" => 6379,                 //端口
        "password" => "",               //密码
        "Db" => 0,                      //数据库
    ),
    /********************************URL设置********************************/
    "HTTPS"                         => FALSE,       //基于https协议
    "URL_REWRITE"                   => 1,           //url重写模式
    "URL_TYPE"                      => 1,           //类型 1:PATHINFO模式 2:普通模式 3:兼容模式
    "PATHINFO_DLI"                  => "/",         //PATHINFO分隔符
    "PATHINFO_VAR"                  => "q",         //兼容模式get变量
    "PATHINFO_HTML"                 => ".html",     //伪静态扩展名
    /********************************url变量********************************/
    "VAR_APP"                       => "a",         //应用变量名，应用组模式有效
    "VAR_CONTROL"                   => "c",         //模块变量
    "VAR_METHOD"                    => "m",         //动作变量
    /********************************项目参数********************************/
    "DEFAULT_NAME"                  => "@",         //应用名称
    "DEFAULT_APP"                   => "index",     //默认项目
    "DEFAULT_CONTROL"               => "Index",     //默认模块
    "DEFAULT_METHOD"                => "index",     //默认方法
    "CONTROL_FIX"                   => "Control",   //控制器文件后缀
    "MODEL_FIX"                     => "Model",     //模型文件名后缀
    "FILTER_FUNCTION"               => "htmlspecialchars", //过滤函数如$this->_get("hdphp")
    /********************************URL路由设置********************************/
    "route" => array(),                             //路由规则
    /********************************缓存控制********************************/
    "CACHE_TYPE"                    => "file",      //类型:file memcache redis
    "CACHE_MEMCACHE"                => array(       //多个服务器设置二维数组
        "host"      => "127.0.0.1",     //主机
        "port"      => 11211,           //端口
        "timeout"   => 1,               //超时时间(单位为秒)
        "weight"    => 1,               //权重
        "pconnect"  => 1,               //持久连接
    ),
    "CACHE_REDIS"                   => array( //多个服务器设置二维数组
        "host"      => "127.0.0.1",     //主机
        "port"      => 6379,            //端口
        "password"  => "",              //密码
        "timeout"   => 1,               //超时时间
        "Db"        => 0,               //数据库
        "pconnect"  => 0,               //持久连接
    ),
    "CACHE_TIME"                    => 3600,        //全局默认缓存时间 0为永久缓存
    "CACHE_SELECT_TIME"             => -1,          //SQL SELECT查询缓存时间 -1为不缓存 0为永久缓存
    "CACHE_SELECT_LENGTH"           => 30,          //缓存最大条数
    "CACHE_TPL_TIME"                => -1,           //模板缓存时间 -1为不缓存 0为永久缓存
    /********************************文件上传********************************/
    "UPLOAD_THUMB_ON"               => 0,           //上传图片缩略图处理
    "UPLOAD_EXT_SIZE"               => array("jpg" => 5000000, "jpeg" => 5000000, "gif" => 5000000,
                                    "png" => 5000000, "bmg" => 5000000, "zip" => 5000000,
                                    "txt" => 5000000, "rar" => 5000000, "doc" => 5000000), //上传类型与大小
    "UPLOAD_PATH"                   => ROOT_PATH . "/upload", //上传路径
    "UPLOAD_IMG_DIR"                => "",       //图片上传目录名
    "UPLOAD_IMG_RESIZE_ON"          => 1,           //上传图片缩放处理,超过以下值系统进行缩放
    "UPLOAD_IMG_MAX_WIDTH"          => 2000000,     //上传图片超过此值，进行缩放
    "UPLOAD_IMG_MAX_HEIGHT"         => 2000000,     //上传图片超过此值，进行缩放
    /********************************图像水印处理********************************/
    "WATER_ON"                      => 1,           //开关
    "WATER_FONT"                    => HDPHP_PATH . "Data/Font/font.ttf",   //水印字体
    "WATER_IMG"                     => HDPHP_PATH . "Data/Image/water.png", //水印图像
    "WATER_POS"                     => 9,           //位置  1~9九个位置  0为随机
    "WATER_PCT"                     => 60,          //透明度
    "WATER_QUALITY"                 => 80,          //压缩比
    "WATER_TEXT"                    => "WWW.HOUDUNWANG.COM", //水印文字
    "WATER_TEXT_COLOR"              => "#f00f00",   //文字颜色
    "WATER_TEXT_SIZE"               => 12,          //文字大小
    /********************************图片缩略图********************************/
    "THUMB_PREFIX"                  => "",          //缩略图前缀
    "THUMB_ENDFIX"                  => "_thumb",    //缩略图后缀
    "THUMB_TYPE"                    => 6,  //生成方式,
                                            //1:固定宽度,高度自增 2:固定高度,宽度自增 3:固定宽度,高度裁切
                                            //4:固定高度,宽度裁切 5:缩放最大边       6:自动裁切图片
    "THUMB_WIDTH"                   => 300,         //缩略图宽度
    "THUMB_HEIGHT"                  => 300,         //缩略图高度
    "THUMB_PATH"                    => "",          //缩略图路径
    /********************************验证码********************************/
    "CODE_FONT"                     => HDPHP_PATH . "Data/Font/font.ttf",       //字体
    "CODE_STR"                      => "123456789abcdefghijklmnpqrstuvwsyz", //验证码种子
    "CODE_WIDTH"                    => 150,         //宽度
    "CODE_HEIGHT"                   => 45,          //高度
    "CODE_BG_COLOR"                 => "#ffffff",   //背景颜色
    "CODE_LEN"                      => 4,           //文字数量
    "CODE_FONT_SIZE"                => 22,          //字体大小
    "CODE_FONT_COLOR"               => "",          //字体颜色
    /********************************分页处理********************************/
    "PAGE_VAR"                      => "page",      //分页GET变量
    "PAGE_ROW"                      => 10,          //页码数量
    "PAGE_SHOW_ROW"                 => 10,          //每页显示条数
    "PAGE_STYLE"                    => 2,           //页码风格
    "PAGE_DESC"                     => array("pre" => "上一页", "next" => "下一页",//分页文字设置
                                            "first" => "首页", "end" => "尾页", "unit" => "条"),
    /********************************模板参数********************************/
    "TPL_ENGINE"                    => "HD",        //模板引擎 HD,Smarty
    "TPL_FIX"                       => ".html",     //模版文件扩展名
    "TPL_TAG_LEFT"                  => "<",         //左标签
    "TPL_TAG_RIGHT"                 => ">",         //右标签
    "TPL_DIR"                       => "Tpl",       //模板目录
    "TPL_TAGS"                      => array(),     //扩展标签,多个标签用逗号分隔
    "TPL_STYLE"                     => "",          //风格
    "TPL_COMPILE"                   => 1,           //模板编译
    "TPL_ERROR"                     => "",          //错误页面
    "TPL_SUCCESS"                   => "",          //正确页面
    /********************************购物车参数********************************/
    "CART_NAME"                     => "cart",      //储存在$_SESSION购物车名称
    /********************************文本编辑器********************************/
    "EDITOR_TYPE"                   => 2,           //复文本编辑器  1 baidu  2 kindeditor
    "EDITOR_STYLE"                  => 1,           //1 完全模式  2 精简模式
    "EDITOR_MAX_STR"                => 2000,        //编辑器最大字数
    "EDITOR_WIDTH"                  => "100%",      //编辑器高度
    "EDITOR_HEIGHT"                 => 300,         //编辑器高度
    "EDITOR_FILE_SIZE"              => 2000000,     //上传图片文件大小

    /********************************RBAC权限控制********************************/
    "RBAC_TYPE"                     => 1,           //1时时认证｜2登录认证
    "RBAC_SUPER_ADMIN"              => "super_admin", //超级管理员SESSION名
    "RBAC_USERNAME_FIELD"           => "username",  //用户名字段
    "RBAC_PASSWORD_FIELD"           => "password",  //密码字段
    "RBAC_AUTH_KEY"                 => "uid",      //用户SESSION名
    "RBAC_NO_AUTH"                  => array(),     //不需要验证的控制器或方法如:array("index/index")表示index控制器的index方法不需要验证
    "RBAC_USER_TABLE"               => "user",      //用户表
    "RBAC_ROLE_TABLE"               => "role",      //角色表
    "RBAC_NODE_TABLE"               => "node",      //节点表
    "RBAC_ROLE_USER_TABLE"          => "user_role", //角色与用户关联表
    "ACCESS_TABLE"                  => "access",    //权限分配表
    /********************************邮箱配置********************************/
    "EMAIL_USERNAME"                => "",          //邮箱用户名
    "EMAIL_PASSWORD"                => "",          //邮箱密码
    "EMAIL_HOST"                    => "",          //smtp地址如smtp.gmail.com或smtp.126.com建议使用126服务器
    "EMAIL_PORT"                    => 25,          //smtp端口 126为25，gmail为465
    "EMAIL_SSL"                     => 0,           //是否采用SSL,126为false,google必须为true
    "EMAIL_CHARSET"                 => "",          //字符集设置,中文乱码就是这个没有设置好 如utf8
    "EMAIL_FORMMAIL"                => "",          //发送人发件箱显示的邮箱址址
    "EMAIL_FROMNAME"                => "后盾网"      //发送人发件箱显示的用户名
);
?>