<?php
/*
	登录接口 
*/

class UserAction extends Action{
	//登录方法
	/*
	用户名密码登录
	*/
	//调用地址：http://www.ydys168.com/index.php?g=Weixin&m=User&a=login&username=用户名&password=密码
	public function login(){
		$db=M('wechat_user');
		if($this->_get()){
			$username=$_GET['username'];
			$pwd=$_GET['password'];
			$res=$db->where(array('username'=>$username))->find();
			if(empty($res)){
				$this->redirect('Weixin/Index/index');
			}else{
				if($res['status']==2){
					$this->redircct('Weixin/Index/index');	
				}else{
					if($res['password']==$pwd){
						session('user_id',$res['id']);								//登录成功信息   2=>登录成功
						$this->redirect('Weixin/Ucenter/index');
					}else{
						$this->redirect('Weixin/Index/index');	
					}	
				}
			}
  }
 }
 
 /*
 微信登录
 */
 //调用地址：http://www.ydys168.com/index.php?g=Weixin&m=User&a=wechatlogin&openid=''&unionid=''&nickname=''&headimgurl=''
 	public function wechatlogin(){
		$db=M('wechat_user');
		$openid=I('get.openid');
		$unionid=I('get.unionid');
		$nickname=I('get.nickname');
		$headimgurl=I('get.headimgurl');
		
		if(strlen($openid)!=28||strlen($unionid)!=28){
			$json['msg']='openid or unionid error';
			echo json_encode($json);
			die();	
		}
		
		$res=$db->where(array('unionid'=>$unionid))->find();

		if(empty($res)){
			$data['type']=2;				//用户类型【APP用户】
			$data['unionid']=$unionid;
			$data['openid']=$openid;
			$data['headimgurl']=urldecode($headimgurl);
			$data['nickname']=urldecode($nickname);
			$data['posttime']=time();
			
			//dump($data);
			//die();
			
			$user_id=$db->data($data)->add();
			
			session('user_id',$user_id);									//登录成功
			$this->redirect('Weixin/Ucenter/index');
		}else{
			if($res['status']==2){
					//登陆失败错误信息 1=>账户异常，已被禁用，如有疑问请咨询平台管理员
					//$json=json_encode(array('error'=>1));
					//echo $json;
					$this->redirect('Weixin/Index/index');								
				}else{
					//登录成功信息   2=>登录成功
					session('user_id',$res['id']);						
					$this->redirect('Weixin/Ucenter/index');
				}
			}
		
		}
 
 
}