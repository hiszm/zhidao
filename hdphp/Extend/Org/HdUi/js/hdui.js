// ====================================================================================
// ===================================--|函数库|--======================================
// ====================================================================================
// .-----------------------------------------------------------------------------------
// |  Software: [HDJS framework]
// |   Version: 2013.07
// |      Site: http://www.hdphp.com
// |-----------------------------------------------------------------------------------
// |    Author: 向军 <houdunwangxj@gmail.com>
// | Copyright (c) 2012-2013, http://houdunwang.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------
// |   License: http://www.apache.org/licenses/LICENSE-2.0
// '-----------------------------------------------------------------------------------
//去除超链接虚线
$(function () {
    $("a").click(function () {
        $(this).trigger("blur")
    });
})
/**
 * 获得对象在页面中心的位置
 * @author hdxj
 * @category functions
 * @param obj 对象
 * @returns {Array} 坐标
 */
function center_pos(obj) {
    var pos = [];//位置
    pos[0] = ($(window).width() - obj.width()) / 2
    pos[1] = $(window).scrollTop() + ($(window).height() - obj.height()) / 2
    return pos
}
// ====================================================================================
// =====================================--|UI|--=======================================
// ====================================================================================
// .-----------------------------------------------------------------------------------
// |  Software: [HDJS framework]
// |   Version: 2013.07
// |      Site: http://www.hdphp.com
// |-----------------------------------------------------------------------------------
// |    Author: 向军 <houdunwangxj@gmail.com>
// | Copyright (c) 2012-2013, http://houdunwang.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------
// |   License: http://www.apache.org/licenses/LICENSE-2.0
// '-----------------------------------------------------------------------------------
/**
 * tab面板使用
 * @author hdxj
 * @category ui
 */
$(function () {
    //首页加载显示第一1个
    $("div.tab ul.tab_menu li:eq(0) a").addClass("action");
    $("div.tab div.tab_content div:eq(0)").addClass("action");
    //点击切换
    $("div.tab ul.tab_menu li").click(function () {
        //改变标题
        $("div.tab ul.tab_menu li a").removeClass("action");
        $("a", this).addClass("action");
        var _id = $(this).attr("lab");
        $("div.tab_content div").removeClass("action");
        $("div.tab_content div#" + _id).addClass("action");
    })
})
/**
 * dialog对话框
 */
$.extend({
    "dialog": function (options) {
        var _default = {
            "type": "success"//类型 CSS样式
            , "msg": "操作成功"//提示信息
            , "timeout": 3//自动关闭时间
            , "close_handler": function () {
            }//关闭时的回调函数
        };
        var opt = $.extend(_default, options);
        //创建元素
        if ($("div.dialog").length == 0) {
            var div = '';
            div += '<div class="dialog">';
            div += '<div class="close">';
            div += '<a href="#" title="关闭">X</a></div>';
            div += '<h2>提示信息</h2>';
            div += '<div class="con ' + opt.type + '">';
            div += opt.msg;
            div += '</div>';
            div += '</div>';
            $(div).appendTo("body");
        }
        $("div.dialog").show();
        var pos = center_pos($(".dialog"));
        $("div.dialog").css({left: pos[0], top: pos[1] - 50});
        //点击关闭dialog
        $("div.dialog div.close a").click(function () {
            $("div.dialog").fadeOut();
            opt.close_handler();
        })
        //自动关闭
        setTimeout(function () {
            $("div.dialog").fadeOut();
            opt.close_handler();
        }, opt.timeout * 1000);
    }
})
/**
 * 模态对话框
 * @category ui
 */
$.extend({
    "modal": function (options) {
        var _default = {
            content: '', width: 650, height: 400, button: true
        };
        var opt = $.extend(_default, options);

        if ($("div.modal").length == 0) {
            var div = '';
            div += '<div class="modal" style="width:' + opt['width'] + 'px;height:' + opt['height'] + 'px">';
            div += '<div class="content" style="height:' + (opt['height'] - (opt.button ? 62 : 0)) + 'px;">';
            div += opt.content;
            div += '</div>';
            if (opt.button) {
                div += '<div class="modal_footer">';
                div += '<a href="#" class="btn">关闭</a>';
                div += '</div>';
            }
            div += '</div>';
            $(div).appendTo("body");
        }
        var pos = center_pos($(".modal"));
        $("div.modal").css({left: pos[0], top: pos[1] - 50});
        //点击关闭modal
        $("div.modal_footer a.btn").click(function () {
            $("div.modal").fadeOut();
        })
    }
});
// ====================================================================================
// ===================================--|表单验证|--=====================================
// ====================================================================================
// .-----------------------------------------------------------------------------------
// |  Software: [HDJS framework]
// |   Version: 2013.07
// |      Site: http://www.hdphp.com
// |-----------------------------------------------------------------------------------
// |    Author: 向军 <houdunwangxj@gmail.com>
// | Copyright (c) 2012-2013, http://houdunwang.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------
// |   License: http://www.apache.org/licenses/LICENSE-2.0
// '-----------------------------------------------------------------------------------
/**
 * 表单验证
 * @category validation
 */
$.fn.extend({
    validation: function (options) {
        var opt = {
            create_span: function () {
                if ($(this).nextAll("span.validation").length == 0) {
                    $(this).after("<span class='validation'></span>");
                }
            },
            success_handler: function (msg) {
                options.create_span.call(this);
                $(this).nextAll("span.validation").removeClass("error").addClass("success").html(msg);
            },
            error_handler: function (msg) {
                options.create_span.call(this);
                $(this).nextAll("span.validation").removeClass("success").addClass("error").html(msg);
            },
            focus_handler: function (msg) {
                options.create_span.call(this);
                $(this).nextAll("span.validation").removeClass("success").removeClass("error").html(msg);
            }
        }
        options = $.extend(opt, options);
        //验证规则
        var method = {
            //比较2个表单
            "confirm": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //比较表单内容是否相等
                    stat = data.obj.val() == $("[name='" + data.rule + "']").val();
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                    $("[name='" + data.rule + "']").blur(function () {
                        data.obj.trigger("blur");
                    })
                }
                return stat;
            },
            //数字
            "num": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    var opt = data.rule.split(/\s*,\s*/);
                    var val = data.obj.val() * 1;
                    //验证表单
                    stat = val >= opt[0] && val <= opt[1];
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //用户
            "user": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    var opt = data.rule.split(/\s*,\s*/);
                    var reg = new RegExp("^[a-z]\\\w{" + (opt[0] - 1) + "," + (opt[1] - 1) + "}$", "i");
                    //验证表单
                    stat = reg.test(data.obj.val());
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //手机
            "phone": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //验证表单
                    stat = /^\d{11}$/.test(data.obj.val());
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //验证证
            "tel": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //验证表单
                    stat = /(?:\(\d{3,4}\)|\d{3,4}-?)\d{8}/.test(data.obj.val());
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //验证证
            "identity": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //验证表单
                    stat = /^(\d{15}|\d{18})$/.test(data.obj.val());
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //网址
            "http": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //验证表单
                    stat = /^(http[s]?:)?(\/{2})?([a-z0-9]+\.)?[a-z0-9]+(\.(com|cn|cc|org|net|com.cn))$/i.test(data.obj.val());
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //中文
            "china": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //验证表单
                    stat = /^[^u4e00-u9fa5]+$/i.test(data.obj.val());
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //最小长度
            "minlen": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //验证表单
                    stat = data.obj.val().length >= data.rule;
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //最大长度
            "maxlen": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //验证表单
                    stat = data.obj.val().length <= data.rule;
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //验证邮箱
            "email": function (data) {
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //验证表单
                    stat = /^([a-zA-Z0-9_\-\.])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/i.test(data.obj.val());
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data);
                }
                return stat;
            },
            //正则验证处理
            "regexp": function (data) {
                var stat = false;
                if (method.check_required(data)) {
                    //是否正则对象
                    if (data.rule instanceof  RegExp) {
                        //是否必须验证
                        var stat = data.rule.test(data.obj.val());
                        ////验证结果处理，提示信息等
                        method.call_handler(stat, data);
                    }
                }
                return stat;
            },
            //验证表单是否必须添写
            "required": function (data) {
                var stat = false;
                //是否必须验证
                if (method.check_required(data)) {
                    //不为空
                    stat = $.trim(data.obj.val()) ? true : false;
                    //验证结果处理，提示信息等
                    method.call_handler(stat, data, options.rules[data.name].message.empty);
                }
                return stat;
            },
            //调用事件处理程序
            call_handler: function (stat, data, msg) {
                var name = data.name;//元素的ID值
                var obj = data.obj;//表单对象
                var rule = data.rule;//规则
                if (stat) {//验证通过
                    //添加表单属性validation
                    obj.attr("validation", 1);
                    //执行成功的回调函数，并传递正确提示信息
                    options.success_handler.call(obj[0], options.rules[name].message.success || "");
                } else {//验证失败
                    obj.attr("validation", 0);//添加表单属性validation
                    //执行失败的回调函数,并传递错误提示信息
                    options.error_handler.call(obj[0], msg || options.rules[name].message.error || "输入错误");
                }
            },
            //是否必须输入处理
            check_required: function (data) {
                //获得required rule规则
                var required = options.rules[data.name]['rule'].required && options.rules[data.name]['rule'].required['rule'];
                //如果没有设置为true，表示必须验证
                required = required === undefined || required;
                //如果required为假，并且表单有值时进行验证
                if (required && data.obj.val() == '') {//必须填写但是为空时的情况
                    method.call_handler(false, data, options.rules[data.name].message.empty);
                    return false;
                } else if (data.obj.val() == '' && !required) {//不需要验证
                    options.success_handler.call(data.obj[0], "");
                    return false;
                }else{
                    return true;
                }
            },
            "ajax": function (data) {
                //默认为失败，Ajax后再处理
                data.obj.attr("validation", 0);
                var stat = false;
                //必须验证时验证
                if (method.check_required(data)) {
                    //比较表单内容是否相等
                    var url = data.rule;
                    var name = data.name;
                    form_obj = $(data.obj.parents("form"));
                    var param = {};
                    param[name] = data.obj.val();
                    //发送异步
                    $.post(url, param, function (result) {
                        //成功时，如果是提交暂停状态则再次提交
                        if (result == 1) {
                            //验证结果处理，提示信息等
                            method.call_handler(1, data);
                            //如果是通过submit调用，则提交
                            if (data.send) {
                                form_obj.trigger("submit", ['send']);
                            }
                        } else {
                            method.call_handler(0, data);
                        }
                    });
                    //验证结果处理，提示信息等

                }
            },
            focus: function (event) {
                var name = event.data.name;
                var obj = $("[name='" + name + "']");//表单对象
                var msg = options.rules[name].message && options.rules[name].message.default || "";
                options.focus_handler.call(obj[0], msg);
            },
            //添加验证设置
            set: function (name) {
                var obj = $("[name='" + name + "']");
                var nodeType = obj[0].nodeName;
                //事件处理类型
                var event = '';
                switch (nodeType) {
                    case "SELECT":
                        event = "change";
                        break;
                    case "RADIO":
                    case "CHECKBOX":
                        event = "change";
                        break;
                    default:
                        event = "blur";
                }
                //如果表单后有span.validation，默认提示信息取其值
                if (obj.nextAll("span.validation").length > 0) {
                    options.rules[name].message.default = obj.nextAll("span.validation").text();
                }
                //success信息如果为空取默认提示信息
                options.rules[name].message.success = options.rules[name].message.success || options.rules[name].message.default || "";
                options['focus_handler'].call(obj, options['rules'][name].message.default || "");
                obj.bind(event, function (event, send) {
                    for (var rule in options.rules[name]['rule']) {
                        //验证方法存在
                        if (method[rule]) {
                            //设置默认值
                            /**
                             * 验证失败 终止验证
                             * 参数说明：
                             * name 表单name属性
                             * obj 表单对象
                             * rule 规则的具体值
                             * send 是否为submit激活的
                             */

                            if (!method[rule]({name: name, obj: obj, rule: options.rules[name]['rule'][rule], send: send}))break;
                        }
                    }
                });

            }
        };
        //处理事件
        var handler = '';//事件类型
        for (var name in options.rules) {
            //表单对象存在
            if ($("[name='" + name + "']").length > 0) {
                //验证表单规则
                method.set(name);
                //获得焦点显示默认值
                $("[name='" + name + "']").bind('focus', {name: name}, method['focus']);
            }
        }
        //处理form
        $(this).bind("submit", function (event, action) {
            if (action) {
                return true;
            }
            //触发验证
            $("*", this).trigger("blur", ["post"]);
            if ($("*[validation='0']").length > 0) {
                return false;
            }
            return true;
        })
    }
});



























