<?php
/*
	代理商登录
*/
class LoginAction extends Action{

	public function index(){
		$config=M('cms_config')->find(1);
		$this->assign('config',$config);
		$this->display();
	}
	//登录方法
	public function login(){
		$db=M('wechat_user');
		if($this->_post()){
			$username=$_POST['username'];
			$pwd=md5($_POST['password']);
			// if($_SESSION['verify']!=md5($yzm)){
			// 	$this->error('验证码错误！');
			// }
			$i=$db->where(array('username'=>$username))->find();
			if(!$i|$i['password']!=$pwd){
				$this->error('用户名或者密码错误！');
			}
			
			if($i['role_id']!=4){
				$this->error('您不是全国代理，无法登录！');	
			}
			//if($i['lock']==1){
//				$this->error('账号已被禁用！');
//			}
			session('service_id',$i['id']);
			session('service_name',$i['username']);
			$this->redirect('Index/index');	
		}
	}
	
	//退出登录
	public function logout(){
		session('service_id',null);
		session('service_name',null);
		$this->redirect('Login/index');
	}
	
	//引入验证码
	public function yzm(){
		import('ORG.Util.Image');
		Image::buildImageVerify(4,1,'png',80,32);
	}
	
}