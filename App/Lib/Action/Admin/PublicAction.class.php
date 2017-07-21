<?php
class PublicAction extends Action{
	//判断用户是否登录
	public function _initialize(){
		if(!isset($_SESSION[C('USER_AUTH_KEY')])){
			$jump=get_curr_url();
			$this->redirect('Admin/Login/index',array('jump'=>base64_encode($jump)));
		}
		$notAuth=in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))||in_array(ACTION_NAME, explode(',', C('NOT_AUTH_ACTION')));
		if(C('USER_AUTH_ON')&& !$notAuth){
			import('ORG.Util.RBAC');
			RBAC::AccessDecision(GROUP_NAME)||$this->error('sorry!您无权访问！');
		}
		//当前登录者所属角色
		$uid=I('session.uid');
		$user_info=M('role_user')->join('right join tp_role on tp_role_user.role_id = tp_role.id')->where("tp_role_user.user_id=$uid")->find();
		$this->assign('role_name',$user_info['remark']);
		//平台信息
		$config=M('cms_config')->find(1);
		$this->assign('config',$config);
	}
	/*
		图片预览
	*/
	public function show_img(){
		$picurl=I('get.picurl','','base64_decode');
		echo "<img src='$picurl' style='width:500px'/>";
	}
	
}