<?php
/**
 * 问题和用户和分类关联模型
 */
class AskModel extends ViewModel{
	public $view = array(
		'user' => array(
			'type' => 'inner',
			'field' => 'username,exp',
			'on'	=> 'user.uid=ask.uid'
			),
		'category' => array(
			'type' => 'inner',
			'field'=> 'title,cid',
			'on'	=> 'category.cid=ask.cid'
			)
		
		);
}