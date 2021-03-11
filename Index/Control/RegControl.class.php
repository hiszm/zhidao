<?php

class RegControl extends Control{

	//异步检测用户名
	public function ajax_username(){

		if(!IS_AJAX) $this->error('页面不存在');
		$username=$this->_POST('username');
		if(M('user')->where(array('username'=>$username))->getField('uid')){
			echo 0;
		} else {
			echo 1;
		}
	}
	
	//异步判断验证码
	public function ajax_code(){
		if(!IS_AJAX) $this->error('页面不存在');
		$code =$this ->_POST('verify','htmlspecialchars,strtoupper');
		if ($code!=$this->_SESSION('code')) {
			echo 0;
		} else {
			echo 1;
		}

	}


	//注册用户
	public function register(){
		if(!IS_POST) $this->error('页面不存在');
		//p($_POST);
		$data =array(
			'username' => $this->_POST('username'),
			'passwd'   => $this->_POST('pwd','md5'),
			'restime'  => time()
			);
		M('user')->add($data);
		$this->success('注册成功....');
	}

	//验证码
	public function code(){

		$code = new code();
		$code ->show();
	}

}