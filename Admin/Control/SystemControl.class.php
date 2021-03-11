<?php
/**
 * 系统管理控制器
 */
class SystemControl extends CommonControl{
	/**
	 * 网站配置
	 */
	public function web_set(){
		$this->display();
	}

	/**
	 * 修改后台用户密码
	 */
	public function passwd(){
		if(IS_POST){
			$this->error("本演示关闭修改后台用户密码功能！");
			$passwdF = $this->_POST('passwdF', 'trim,htmlspecialchars');
			$passwdS = $this->_POST('passwdS', 'trim,htmlspecialchars');

			if(strlen($passwdF)<6) $this->error("密码不得少于6位");
			if($passwdF != $passwdS) $this->error('两次密码不相同');

			$aid = $this->_SESSION('aid', 'intval');
			M('admin')->where(array('aid'=>$aid))->save(array('passwd'=>md5($passwdF)));

			$this->success('修改成功！');
		}

		$this->display();
	}

}