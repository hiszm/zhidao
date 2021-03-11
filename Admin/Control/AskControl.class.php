<?php
/**
 * 问题管理控制器
 */
class AskControl extends CommonControl{
	/**
	 * 问题展示
	 */
	public function ask(){
		$w = $this->_GET('w', 'intval');
		switch ($w) {
			case 1:
				$where = array('solve'=>0);
				break;
			case 2:
				$where = array('solve'=>1);
				break;
			case 3:
				$where = array('answer'=>0);
				break;
			
			default:
				$where = null;
				break;
		}
		$db = M('ask');
		$count = $db->where($where)->count();
		$page = new page($count, 10, 4, 2);

		$field = 'content,time,answer,reward,asid';
		$ask = $db->where($where)->field($field)->select($page->limit());

		$this->assign('ask', $ask);
		$this->assign('page', $page->show());
		$this->assign('count', $count);

		$this->display();
	}
	/**
	 * 删除问题
	 */
	public function del_ask(){
		$asid = $this->_GET('asid', 'intval');
		$where = array('asid'=>$asid);

		$db = M('user');

		//提问者扣除金币
		$ask = M('ask')->where($where)->field('solve,uid')->find();
		$askUid = $ask['uid'];
		$db->dec('point', "uid=$askUid", C('GOLD_DEL_ASK'));

		if($ask['solve']){
			$answerUid = M('answer')->where(array('asid'=>$asid, 'accept'=>1))->getField('uid');
			$db->dec('point', "uid=$answerUid", C('GOLD_DEL_ASK'));
		}

		M('ask')->where($where)->delete();
		M('answer')->where($where)->delete();


		$this->success('删除成功！');
	}

}