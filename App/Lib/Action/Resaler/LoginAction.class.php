<?php
/*
	后台登录控制【分销商】
*/
class LoginAction extends Action{
	//后台登录模板输出
	public function index(){
		$this->display('logon');
	}
	//登录方法
	public function login (){
		if($this->_post()){
			$username=$_POST['username'];
			$pwd=md5($_POST['password']);
			
			$info=M('wechat_user')->where(array('username'=>$username,'role_id'=>3))->find();			//分销商
			
			if($info['password']!=$pwd){
				$this->error('用户名或者密码错误！');
			}
			$_SESSION['resaler_id']=$info['id'];			//分销商id
			$this->redirect('Index/index');	
		}
	}
	//退出登录
	public function logout(){
		unset($_SESSION['resaler_id']);
		$this->redirect('Login/index');
	}
	//引入验证码
	public function yzm(){
		import('ORG.Util.Image');
		Image::buildImageVerify(4,1,'png',80,32);
	}
	
}