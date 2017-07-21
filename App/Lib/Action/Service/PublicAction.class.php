<?php
class PublicAction extends Action{
	public $service_id;					//代理商ID
	//判断用户是否登录
	public function _initialize(){
		$this->service_id=I('session.service_id');
		if(!isset($this->service_id)||empty($this->service_id)){
			$this->redirect('Login/index');
		}
		//站点配置信息
		$config=M('cms_config')->find(1);
		$this->assign('config',$config);
		$this->assign('sid',$this->service_id);		//店铺id
	}
	
	/*
		图片预览
	*/
	public function show_img(){
		$picurl=I('get.picurl','','base64_decode');
		echo "<img src='$picurl' style='width:500px'/>";
	}
}