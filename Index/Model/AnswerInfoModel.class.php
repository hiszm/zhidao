<?php
/**
 * 答案和问题和分类关联模型
 */
class AnswerInfoModel extends ViewModel{
	public $table = 'answer';
	public $view = array(
		'ask' => array(
			'type' => 'left',
			'field' => 'answer,asid,cid',
			'on'	=> 'ask.asid=answer.asid'
			),
		'category' => array(
			'type'	=> 'left',
			'field' => 'title',
			'on'	=> 'ask.cid=category.cid'
			),
		);
}