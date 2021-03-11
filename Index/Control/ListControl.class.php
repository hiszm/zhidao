<?php
/**
 * 列表控制器
 */
class ListControl extends CommonControl{
	/**
	 * 默认列表页显示
	 * @return [type] [description]
	 */
	public function index(){



		  $this->assign_data();

























		//$this->assign_data();
		
		$cid = $this->_GET('cid', 'intval');

		$this->top_cate();
		
		$db = M('category');

		$fatherCate = $this->father_cate($db->select(), $cid);
		$this->assign('fatherCate', array_reverse($fatherCate));

		$sonCate = $db->where(array('pid'=>$cid))->select();
		
		if(empty($sonCate)){
			$pid = $db->where(array('cid'=>$cid))->getField('pid');
			$cid = $pid;
			$sonCate = $db->where(array('pid'=>$cid))->select();
		}

		$this->assign('sonCate', $sonCate);



		$where = $this->_GET('where', 'intval');
		if(isset($where) && $where<4){
			$condition = $where;
		} else {
			$condition = 0;
		}
			
		switch ($condition) {
			case 0:
				$where = array('solve'=>0, 'reward'=>array('elt'=>20));
				break;
			case 1:
				$where = array('solve'=>1);
				break;
			case 2:
				$where = array('solve'=>0, 'reward'=>array('gt'=>20));
				break;
			case 3:
				$where = array('solve'=>0, 'answer'=>0);
				break;

		}
		if($cid !=0 ){
			$where['cid'] = $cid;
		}



		$count = M('ask')->where($where)->count();//统计问题进行分类
		$page = new page($count, 5, 5, 2);
		$this->assign('page', $page->show());

		// p($where);
		$field = 'reward,content,answer,time,title,asid,ask.cid';
		$ask = K('ask')->join('category')->where($where)->field($field)->select($page->limit());

		$this->assign('ask', $ask);


		$this->display();
	}
}