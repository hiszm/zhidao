<?php
/**
 * 问题分类管理控制器
 */
class CategoryControl extends CommonControl{
	/**
	 * 问题列表
	 */
	public function category(){
		$cate = M('category')->select();
		$cate = $this->limitless($cate);
		// p($cate);
		$this->assign('cate', $cate);
		$this->display();
	}

	/**
	 * 添加子分类
	 */
	public function add_cate(){
		// p($_GET);
		if(IS_POST){
			$data = array(
				'title' => $this->_POST('title'),
				'pid'	=> $this->_POST('pid', 'intval'),
				);
			M('category')->add($data);
			$this->success('添加成功！');
		}
		$this->display();
	}

	/**
	 * 修改分类
	 */
	public function edit_cate(){
		if(IS_POST){

			$cid = $this->_POST('cid', 'intval');
			$title = $this->_POST('title');

			M('category')->where(array('cid'=>$cid))->save(array('title'=>$title));
			$this->success('修改成功！');

		}
		$cid = $this->_GET("cid", 'intval');
		$cate = M('category')->where(array('cid'=>$cid))->find();
		$this->assign('cate', $cate);
		$this->display();
	}

	/**
	 * 删除分类
	 */
	public function del_cate(){
		$cid = $this->_GET('cid', 'intval');
		$cate = M('category')->field('cid,pid')->select();
		$cate = $this->son_cate($cate, $cid);
		// p($cate);
		$cate[] = $cid;
		$where = implode(',', $cate);
		M('category')->in($where)->delete();
		$this->success('删除成功！');
	}


	/**
	 * 添加顶级分类
	 */
	public function add_top_cate(){
		if(IS_POST){
			$title = $this->_POST('title');
			M('category')->add(array('title'=>$title));
			$this->success('添加成功！');
		}
		
		$this->display();
	}

}