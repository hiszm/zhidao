<?php

//公共的 控制器

class CommonControl extends Control{



	public function assign_data(){

	 	$this->right_info();
        $this->eve_star();
        $this->his_star();
        $this->helper();
        $this->top_cate();
	}

//右侧用户的登录信息
		//
		

	public function right_info(){

			$uid= $this->_SESSION('uid','intval');
			if($uid){
				$field='face,exp,point,answer,ask,accept';
				$userInfo=M('user')->where(array('uid'=>$uid))->field($field)->find();
			

				$userInfo['face']=$this->face($userInfo);
				$userInfo['lv']=$this->exp_to_level($userInfo);
				$userInfo['ratio']=$this->ratio($userInfo);
				$this->assign('userInfo',$userInfo);
				
			}

	}


	//本日回答最多的人
	
	public function eve_star(){

		$zero=strtotime(date('Y-m-d'));
		$field='face,username,user.uid,exp,answer,user.accept';
		$eveStar=K('answer')->where(array('time'=>array('gt'=>$zero)))->field($field)->group('username')->order('COUNT(username) DESC')->find();

		if(!empty($eveStar)){
				$eveStar['face']=$this->face($eveStar);
				$eveStar['lv']=$this->exp_to_level($eveStar);
				$eveStar['ratio']=$this->ratio($eveStar);
		}
		$this->assign('eveStar',$eveStar);

	}




	//历史回答最多的人
	//
	public function his_star(){

		$field='face,username,user.uid,exp,answer,user.accept';
		$hisStar=K('answer')->field($field)->order('answer DESC')->find();
		if(!empty($hisStar)){
				$hisStar['face']=$this->face($hisStar);
				$hisStar['lv']=$this->exp_to_level($hisStar);
				$hisStar['ratio']=$this->ratio($hisStar);
		}
		$this->assign('hisStar',$hisStar);

	}

	//助人光荣榜

	public function helper(){

		$field='uid,username,accept';
		$helper=M('user')->field($field)->order('accept DESC')->limit(10)->select();
		$this->assign('helper',$helper);
	}



	public function top_cate(){

		//顶级分类
		
		$topCate=M('category')->where(array('pid'=>0))->select();
		$this->assign('topCate',$topCate);

		//累积提问
		$askNum=M('ask')->count();
		$this->assign('askNum',$askNum);
	}

	//获得父级分类
	
	public function father_cate($arr,$pid){

		$array=array();

		foreach ($arr as $v) {
			if($v['cid']==$pid){
				$array[]=$v;
				$array=array_merge($array,$this->father_cate($arr,$v['pid']));
			}
		}

		return $array;
	}

	//经验转换等级
	public function exp_to_level($user){

		$exp =$user['exp'];
		for($i=0;$i<21;$i++){

			if($exp<=C('LV'.$i)){
				return $i;
			}
		}

		if($exp>C('LV20')){
			return  20;
		}
	}


	public  function face($user){
		if(!empty($user['face'])){

			return $user['face'];
		}
		return "./Index/Tpl/Public/Images/noface.gif";
	}

	/**
	 * 采纳率换算
	 */
	public function ratio($user){
		if(!empty($user) && $user['answer']){
			$num = $user['accept'] / $user['answer'];
			$ratio = (sprintf("%.2f", $num)) * 100;
		} else {
			$ratio = 0;
		}

		return $ratio;
	}

}
