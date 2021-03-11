<?php
/**
 * 规则管理控制器
 */
class RulesControl extends CommonControl{

	/**
	 * 金币奖励规则
	 */
	public function rule_point(){
		$this->display();
	}

	/**
	 * 级别规则管理
	 */
	public function rule_level(){
		$this->display();
	}

}