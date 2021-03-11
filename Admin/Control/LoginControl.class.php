<?php
/**
 * 登录控制器
 */
class LoginControl extends Control{
	/**
	 * 显示登录页
	 */
	public function index(){
		$this->display();
	}

	/**
	 * 验证码
	 */
	public function code(){
		$code = new code();
		$code->show();
	}

	/**
	 * 后台会员登录
	 */
	public function login(){
		if(!IS_POST) $this->error('页面不存在');
		
		$username = $this->_POST('userName');
		$code = $this->_POST('verify', 'htmlspecialchars,strtoupper');
		if($code != $_SESSION['code']) $this->error('验证码错误！');

		$db = M('admin');
		$user = $db->where(array("username"=>$username))->field('passwd,lock,aid')->find();

		if($user['lock'] == 1) $this->error('您已经被锁定，请联系管理员');

		$passwd = $this->_POST('psd', 'md5');
		if($passwd != $user['passwd']) $this->error('用户名或者密码错误！');


		$data = array(
			'logintime' => time(),
			'loginip' 	=> ip::getClientIp(),
			);

		$db->where(array('username'=>$username))->save($data);

		session('adminname', $username);
		session('aid', $user['aid']);

		$this->success('登录成功！正在为您跳转.....', 'Index/index');
	}


	/**
	 * 退出
	 */
	public function out(){
		session_unset();
		session_destroy();
		$this->success('退出成功！');
	}


}