<?php
/*
	登录接口 
*/

class UserAction extends Action{
	//登录方法
	/*
	用户名密码登录
	*/
	//调用地址：http://www.*******.com/index.php?g=Index&m=user&a=login&username='用户名'&password='密码'
	public function login(){
		$db=M('wechat_user');
		if($this->_get()){
			$username=$_GET['username'];
			$pwd=$_GET['password'];
			$res=$db->where(array('username'=>$username))->find();
			if(empty($res)){
				 $json=json_encode(array('error'=>0));	
				 echo $json;							//登陆失败错误信息 0=>用户名或密码不正确
			}else{
				if($res['status']==2){
					$json=json_encode(array('error'=>1)); 
			 		echo $json;									//登陆失败错误信息 1=>账户异常，已被禁用，如有疑问请咨询平台管理员	
				}else{
					if($res['password']==$pwd){
						$json=json_encode(array('error'=>2)); 
			 			echo $json;
						session('user_id',$res['id']);								//登录成功信息   2=>登录成功
					}else{
						$json=json_encode(array('error'=>0));	
					    echo $json;								//登陆失败错误信息 0=>用户名或密码不正确
					}	
				}
			}
  }
 }
 
 /*
 微信登录
 */
 //调用地址：http://www.*******.com/index.php?g=Index&m=user&a=wechatlogin&openID='openID'
 	public function wechatlogin(){
		$db=M('wechat_user');
		$wechatid=$_GET['openID'];
		$res=$db->where(array('wechatid'=>$wechatid))->find();
		if(empty($res)){
			$data['wechatid']=$wechatid;
			$data['posttime']=time();
			$db->data($data)->add();
			$row=$db->where(array('wechatid'=>$wechatid))->find();
			$json=json_encode(array('error'=>2));
			echo $json;
			session('user_id',$row['id']);									//登录成功
			}else{
				if($res['status']==2){
					$json=json_encode(array('error'=>1));
					echo $json;								//登陆失败错误信息 1=>账户异常，已被禁用，如有疑问请咨询平台管理员
					}else{
					$json=json_encode(array('error'=>2));
					echo $json;
					session('user_id',$res['id']);						//登录成功信息   2=>登录成功
						}
				}
		
		}
 
 
}