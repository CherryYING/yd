<?php
/*

	基础类
	
*/

class BaseAction extends Action{
	public $user_id;
	public $user_info;
	public function _initialize(){
		
		if(is_mobile()){
			$this->redirect('Weixin/Index/index');
		}
		
		$webinfo=M('cms_config')->find(1);
		$this->assign('webinfo',$webinfo);
		$this->user_id=session('user_id');
		$this->user_info=M('wechat_user')->where(array('id'=>$this->user_id))->find();
		$this->assign('user_id',$this->user_id);
		$this->assign('user_info',$this->user_info);
	}
	public function _empty(){
		$this->redirect('Index/index');
	}
	
}