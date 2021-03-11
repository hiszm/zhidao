<?php

class MemberControl extends CommonControl{


	public function __construct(){
		$this->top_cate();
		$this->_left_info();
	}



	//
	//默认会员中心
	public function index(){
		$uid = $this->_GET('uid', 'intval');
		$where = array('uid'=>$uid);
		//我的提问
		$field = 'content,title,answer,time,asid,ask.cid';
		$ask = K('ask')->join('category')->where($where)->field($field)->order('time DESC')->limit(5)->select();
		$this->assign('ask', $ask);

		//我的回答
		$field = 'answer.content,title,ask.answer,answer.time,ask.asid,ask.cid';
		$answer = K('answerInfo')->field($field)->where($where)->order('time DESC')->limit(5)->select();
		// p($answer);
		$this->assign('answer', $answer);

		$this->display();

	}


	//我的提问
	public function my_ask(){
		$uid = $this->_GET('uid', 'intval');

		$field = 'content,title,answer,time,asid,ask.cid';
		$where = array(
			'uid' => $uid,
			'solve' => 0
			);
		if($this->_GET('w', 'intval') == 1){
			$where['solve'] = 1;
		}//来判断是否 被解决
		$db = M('ask');
		$kdb =  K('ask')->join('category');

		$noSolvePage = new page($db->where($where)->count(), 5, 5, 2);
		$noSolve = $kdb->field($field)->where($where)->order('time DESC')->select($noSolvePage->limit());

		$this->assign("noSolve", $noSolve);
		$this->assign("noSolvePage", $noSolvePage->show());

		$this->display();
	}



	/**
	 * 我的回答
	 */
	public function my_answer(){
		$uid = $this->_GET('uid', 'intval');
		$db = M("answer");
		$where = array('uid'=>$uid);
		$field = 'answer.content,title,ask.answer,answer.time,ask.asid,ask.cid';
		if($this->_GET('w', 'intval') == 1){
			$where['accept'] = 1;
		}

		$page = new page($db->where($where)->count(), 5, 5, 2);
		$answer = K('answerInfo')->where($where)->field($field)->order('time DESC')->select($page->limit());

		$this->assign('answer', $answer);
		$this->assign('page', $page->show());

		$this->display();
	}

	/**
	 * 我的等级
	 */
	public function my_level(){
		$uid = $this->_GET('uid', 'intval');
		$user = M('user')->where(array('uid'=>$uid))->field('exp')->find();
		$level = $this->exp_to_level($user);
		$nextExp = C('LV'. ($level+1)) - $user['exp'];
		$nextExp = ($nextExp<0) ? 0 : $nextExp;

		$this->assign('level', $level);
		$this->assign('nextExp', $nextExp);

		$levelExp = array();
		for ($i=0; $i < 21; $i++) { 
			$levelExp[$i] = C('LV'. $i);
		}
		$this->assign('exp', $user['exp']);
		$this->assign('levelExp', $levelExp);

		$this->display();
	}

	/**
	 * 我的金币
	 */
	public function my_point(){
		$this->display();
	}


	/**
	 * 我的头像
	 */
	public function my_face(){
		$uid = $this->_GET('uid', 'intval');
		$db =  M('user');
		$where = array('uid'=>$uid);
		if(IS_POST){
			//$this->error('本演示已关闭上传头像功能');
			$upload = new upload();
			$uploadInfo = $upload->upload();

			$oldFace = $db->where($where)->getField('face');//旧的图片地址
			$oldFace = './' . substr($oldFace, strpos($oldFace, 'upload'));

			if(is_file($oldFace)){
				if(!unlink($oldFace))
					$this->error('没有权限！');
			}
			
			$face = './'. '/' . $uploadInfo[0]['path'];
			$db->where($where)->save(array('face'=>$face));
			$this->success('上传成功！');
		}
		$user = $db->where($where)->field('face')->find();
		$this->assign('face', $this->face($user));

		$this->display();
	}
	//左侧用户信息
	
	private function _left_info(){
		$uid = $this->_GET('uid', 'intval');
		$field = 'face,username,point,exp,ask,answer,accept';
		$member = M('user')->where(array('uid'=>$uid))->field($field)->find();

		if(!empty($member)){
			$member['face'] = $this->face($member);
			$member['ratio'] = $this->ratio($member);
			$member['lv'] = $this->exp_to_level($member);

			$this->assign('member', $member);
		} else {
			$this->error('用户不存在！');
		}

		//第三人称
		$rank = isset($_SESSION['uid']) && $uid == $_SESSION['uid'] ? '我' : 'TA';
		$this->assign('rank', $rank);
	}
}