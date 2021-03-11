<?php
/**
 * 答案控制器
 */
class AnswerControl extends CommonControl{
	/**
	 *答案展示
	 */
	public function answer(){
		$w = $this->_GET('w', 'intval');
		switch ($w) {
			case 1:
				$where = array('accept'=>0);
				break;
			case 2:
				$where = array('accept'=>1);
				break;
			
			default:
				$where = null;
				break;
		}
		$db = M("answer");
		$count = $db->where($where)->count();

		$page = new page($count, 10, 5, 2);

		$field = 'anid,content,time';
		$answer = $db->where($where)->field($field)->select($page->limit());

		$this->assign('count', $count);
		$this->assign('page', $page->show());
		$this->assign('answer', $answer);

		$this->display();
	}

	/**
	 * 删除答案
	 */
	public function del_answer(){
		$anid = $this->_GET('anid', 'intval');
		$where = array('anid'=>$anid);
		$db = M('user');

		$answer = M('answer')->where($where)->field('asid,uid,accept')->find();
		$anUid = $answer['uid'];

		$db->dec('point', "uid=$anUid", C('GOLD_DEL_ANSWER'));

		if($answer['accept']){
			$askDb = M('ask');
			$where = array('asid'=>$answer['asid']);

			$askUid = $askDb->where($where)->getField('uid');
			$db->dec('point', "uid=$askUid", C('GOLD_DEL_ANSWER'));

			$askDb->where($where)->save(array('sovle'=>0));
		}


		M("answer")->where(array('anid'=>$anid))->delete();

		$this->success('删除成功！');
	}

}