<?php
/**
 * 问题操作控制器
 */
class ShowControl extends CommonControl{
	/**
	 * 问题显示
	 */
	public function index(){
		//分配top数据
		$this->assign_data();
		$cid = $this->_GET('cid', 'intval');
		$cate = M('category')->select();

		//获得父级分类
		$father = $this->father_cate($cate, $cid);
		$this->assign('fatherCate', array_reverse($father));

		$asid = $this->_GET('asid', 'intval');
		$ask = K('ask')->where(array('asid'=>$asid))->find();
		$this->assign('ask', $ask);
		$this->assign('lv', $this->exp_to_level($ask));
		// p($ask);
		
		//显示当前问题答案
		$answerDb = K('answer');
		$count = $answerDb->where(array('asid'=>$asid))->count();
		$page = new page($count, 5, 4, 2);
		$answer = $answerDb->where(array('asid'=>$asid,'accept'=>0))->select($page->limit());

		$this->assign('face', $this->face($answer));
		$this->assign('page', $page->show());
		$this->assign('count', $count);
		$this->assign('answer', $answer);

		//满意回答信息
		$where = array(
			'asid'  => $asid,
			'accept'=>1
			);
		$answerOk = K('answer')->where($where)->find();
		$this->assign('answerOk', $answerOk);
		$this->assign('ratio', $this->ratio($answerOk));
		$this->assign('lvOk', $this->exp_to_level($answerOk));
		$this->assign('faceOk', $this->face($answerOk));

		//相关问题
		$where = array(
			'solve' => 0,
			'cid'   => $cid,
			'asid'  => array('neq' => $asid)
			);
		$alike = M('ask')->where($where)->limit(5)->select();
		$this->assign('alike', $alike);

		$this->display();
	}

	public function answer(){
		if(!IS_POST) $this->error('页面不存在！');
		//组合数据
		$uid = $this->_SESSION('uid', 'intval');
		$asid = $this->_POST('asid', 'intval');
		$data = array(
			'asid'  =>$asid,
			'uid'	=> $uid,
			'time'  => time(),
			'content'=>$this->_POST('content')
			);
		M('answer')->add($data);
		//修改用户信息（金币，经验，回答数）
		$userDb = M('user');
		$userDb->inc('point', "uid=$uid", C('GOLD_ANSWER'));
		$userDb->inc('answer', "uid=$uid", 1);
		$userDb->inc('exp', "uid=$uid", C('LV_ANSWER'));

		M('ask')->inc('answer', "asid=$asid", 1);

		$this->success('回答成功！');
	}


	/**
	 * 采纳
	 */
	public function accept(){
		//修改答案为采纳
		$anid = $this->_GET('anid', 'intval');
		M('answer')->where(array('anid'=>$anid))->save(array('accept'=>1));

		//修改问题为解决
		$asid = $this->_GET('asid', 'intval');
		M('ask')->where(array('asid'=>$asid))->save(array('solve'=>1));

		//修改提问用户信息
		$askUid = M('ask')->where(array('asid'=>$asid))->getField('uid');
		$userDb = M('user');

		$userDb->inc('point', "uid=$askUid", C('GOLD_ACCEPT')); 
		$userDb->inc('exp', "uid=$askUid", C('LV_ACCEPT'));

		//修改回答用户信息
		$reward = M('ask')->where(array('asid'=>$asid))->getField('reward');

		$anUid = M('answer')->where(array('anid'=>$anid))->getField('uid');
		$userDb->inc('point', "uid=$anUid", C('GOLD_ACCEPT'));
		$userDb->inc('exp', "uid=$anUid", C('LV_ACCEPT'));
		$userDb->inc('accept', "uid=$anUid", 1);
		$userDb->inc('point', "uid=$anUid", $reward);

		$this->success('采纳成功！');

	}

}