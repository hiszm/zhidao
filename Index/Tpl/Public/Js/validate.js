
var validate = {
	username : false,
	pwd		 : false,
	pwded    : false,
	verify   : false,
	loginUsername : false,
	loginPwd : false

}
var msg = '';
	
//验证注册表单
$(function(){
	//点击更换验证码图片
	$('#verify-img').click(function() {
		$(this).attr('src', APP + '/Reg/code/' + Math.random());
	});
	var register = $('form[name=register]');
	register.submit(function() {
		var isOk = validate.username && validate.pwd && validate.pwded && validate.verify;
		if(isOk) {
			return true;
		}
		//点击提交按钮依次触发失去焦点再次验证
		$('input[name=username]', register).trigger('blur');
		$('input[name=pwd]', register).trigger('blur');
		$('input[name=pwded]', register).trigger('blur');
		$('input[name=verify]', register).trigger('blur');
		return false;

	});
	//-------验证用户名-------
	$('input[name=username]', register).blur(function(){
		var username = $(this).val();
		var span = $(this).next();
		//不能为空
		if(username == ''){
			msg = '用户名不能为空！';
			span.html(msg).addClass('error');
			validate.username = false;
			return;
		}
		//正则判断
		if(!/^\w{2,14}$/g.test(username)){
			msg = '必须是2-14个字符，字母，数字，下划线';
			span.html(msg).addClass('error');
			validate.username = false;
			return;
		}

		//异步验证用户名是否存在
		$.post(APP + '/Reg/ajax_username', {username : username}, function(status){
			if(status){
				msg = '';
				span.html(msg).removeClass('error');
				validate.username = true;
			} else {
				msg = '用户名已经存在！';
				span.html(msg).addClass('error');
				validate.username = false;
			}
		}, 'json');


	})
	//---------验证密码-------------
	$('input[name=pwd]', register).blur(function(){
		var pwd = $(this).val();
		var span = $(this).next();
		//不能为空
		if(pwd == ''){
			msg = '密码不能为空';
			span.html(msg).addClass('error');
			validate.pwd = false;
			return;
		}
		//正则判断
		if(!/^\w{6,20}$/g.test(pwd)){
			msg = '密码必须由6-20个字母，数字，或者下划线组成';
			span.html(msg).addClass('error');
			validate.pwd = false;
			return;
		}

		msg = '';
		validate.pwd = true;
		span.html(msg).removeClass('error');

	})

	//确认密码
	$('input[name=pwded]', register).blur(function(){
		var pwded = $(this).val();
		var span = $(this).next();
		//确认密码不能为空
		if(pwded == ''){
			msg = '请确认密码';
			span.html(msg).addClass('error');
			validate.pwded = false;
			return;
		}
		//两次密码是否相同
		if(pwded != $('input[name=pwd]', register).val()){
			msg = '两次密码不一致！';
			span.html(msg).addClass('error');
			validate.pwded = false;
			return;
		}
		msg = '';
		span.html(msg).removeClass('error');
		validate.pwded = true;


	})

	//-------验证验证码-----
	$('input[name=verify]', register).blur(function() {
		var verify = $(this).val();
		var span = $(this).next().next();
		//不能为空
		if(verify == ''){
			msg = '请输入验证码';
			span.html(msg).addClass('error');
			validate.verify = false;
			return;
		}
		//异步验证码判断
		$.post(APP + "/Reg/ajax_code", {verify : verify}, function(status){
			if(status) {
				msg = '';
				span.html(msg).removeClass('error');
				validate.verify = true;
			} else {
				msg = '验证码错误！';
				span.html(msg).addClass('error');
				validate.verify = false;
			}
		}, 'json');




	});

	//登录form 表单验证 
	var login = $('form[name=login]');
	//登录提交事件
	login.submit(function() {
		if(validate.loginUsername && validate.loginPwd){
			return true;
		} 
		//依次触发失去焦点动作
		$('input[name=username]', login).trigger('blur');
		$('input[name=pwd]', login).trigger('blur');
		return false;
	});

	//验证登录用户名
	$('input[name=username]', login).blur(function() {
		var username = $(this).val();
		var span = $('#login-msg');
		//为空的情况
		if(username == ''){
			span.html('请填入账号');
			validate.loginUsername = false;
			return;
		}



	});

	//验证密码
	$('input[name=pwd]', login).blur(function(){
		var pwd = $(this).val();
		var span = $('#login-msg');
		//为空的情况
		if(pwd == ''){
			span.html('请输入密码');
			validate.loginPwd = false;
			return;
		}
		var data = {
			username : $('input[name=username]', login).val(),
			pwd : pwd 
		}
		$.post(APP + '/Login/ajax_login', data, function(status){
			if(status){
				span.html('');
				validate.loginUsername = true;
				validate.loginPwd = true;
			} else {
				span.html('用户名或者密码不正确');
				validate.loginUsername = false;
				validate.loginPwd = false;
			}
		}, 'json');


	})





})

