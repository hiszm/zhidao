<?php

//登录控制器

class LoginControl extends Control{
	//Ajax 的登录验证
	
	public function ajax_login(){
		if(!IS_AJAX) $this->error('页面不存在....');

		$username = $this->_POST('username');
		$pwd = $this->_POST('pwd','md5');
		$passwd = M('user')->where(array('username'=>$username))->getField('passwd');
		if($pwd != $passwd){
			echo 0;
		}else{
			echo 1;
		}
	

	}




	//登录的方法
	public function login(){
		if(!IS_POST) $this->error('页面存在......');
		//p($_POST);
		$username=$this->_POST('username');
		$pwd=$this->_POST('pwd','md5');
		$user=M('user')->where(array('username'=>$username))->Field('passwd,lock,uid')->find();
		if(empty($user))$this->error('用户不存在');
		if($pwd !=$user['passwd']) $this->error('用户名或者密码不正确');
		if($user['lock']==1) $this->error('您已经被锁定,请联系管理员');

		$this->eve_exp($user['uid']);


		$loginData=array(
			'logintime'=>time(),
			'loginip'=>ip::getClientIp(),
			);
		M('user')->where(array('uid'=>$user['uid']))->save($loginData);

		//P($_POST);
		$auto=$this->_POST('auto');
		if($auto=='on'){
			setcookie(session_name(),session_id(),C('COOKIE_TIME'),'/');
		}

		session('username',$username);
		session('uid',$user['uid']);

		$this->success('登录成功! 正在返回...');

	}

	//每天登录的增加的检验
	private function eve_exp($uid){
			//获取当天的时间戳
			$zero = strtotime(date('Y-m-d'));
			//获取用户的登录时间
			$logintime=M('user')->where(array('uid'=>$uid))->getField('logintime');
			//时间比对
			if($logintime < $zero){
				M('user')->inc('exp',"uid=$uid",C('LV_LOGIN'));
			}
		}


	public function out(){
		unset($_SESSION['username']);
		unset($_SESSION['uid']);
		$this->success('退出成功');
			
	}
}