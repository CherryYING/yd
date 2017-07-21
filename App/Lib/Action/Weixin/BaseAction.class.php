<?php
/*
	基控制器【微信商城】
*/
class BaseAction extends Action{
		
    public $user_id;		//登录用户id
    public $user_info;		//登录用户信息
	
	public $wxauth;			//微信网页认证obj
	public $wx_userinfo;	//当前微信用户信息
	
	
    public function _initialize(){
		$sys=M('sys_status')->find(1);
		if($sys['web_status']==2){
			$this->redirect('Error/err_sys');
		}
		if(!is_mobile()){				//非移动终端
			//$this->redirect('Index/Index/index');
		}
		
        if(!is_weixin()) {				//浏览器
		   //session('user_id',1514);
		   $this->user_id=session('user_id');
		   $this->user_info=M('wechat_user')->where(array('id'=>$this->user_id))->find();
        }else{							//微信处理逻辑
			if(empty($_SESSION['wx_userinfo'])){
				$this->wxOauth();		//微信网页授权
			}else{
				$this->check_user();	//检查当前微信用户是否已注册	
			}
			$this->user_id=session('user_id');	
			$this->user_info=M('wechat_user')->where(array('id'=>$this->user_id))->find();
		}
		$this->assign('user_id',$this->user_id);
		$this->assign('user_info',$this->user_info);
		
		if($this->user_info['status']==2){
			$this->redirect('Error/err_user');	
		}
		
		$this->init();					//系统公共数据初始化
    }
    
   
    /*
		微信网页授权
	*/
    public function wxOauth(){
        import("@.ORG.WxOAuth");
		//公众号配置信息
		$conf=M('wechat_config')->find(1);	
        $this->wxauth=new WxOAuth($conf);
        $redirect_uri=get_curr_url();
        $auth_url=$this->wxauth->get_authorize_url($redirect_uri);
		if(!isset($_GET['code'])&&empty($_SESSION['wx_userinfo'])){
			redirect($auth_url);
		}else{
			$access_token=$this->wxauth->get_access_token($_GET['code']);

			$info=$this->wxauth->get_user_info($access_token['access_token'],$access_token['openid']);
			if(!$access_token['openid']){
				redirect($auth_url);
			}else{
				$_SESSION['wx_userinfo']=$info;
			
				if(is_array($_SESSION['wx_userinfo'])){
					$this->check_user();				//检查信息用户信息是否存在
				}
			}
		}
		
    }
	
	public function check_user(){
		//推广会员UID
		$parent_id=I('get.tid');
		$this->wx_userinfo=$_SESSION['wx_userinfo'];
		//$where=array('wechatid'=>$this->wx_userinfo['openid']);
		$where="wechatid='{$this->wx_userinfo['openid']}' OR unionid='{$this->wx_userinfo['unionid']}'";
		//$i=M('wechat_user')->where($w)->find();
		//file_put_contents('user.txt',var_export($i,true));
		//file_put_contents('sql.txt',M('wechat_user')->getlastsql());
		
		$reg_info=M('wechat_user')->where($where)->find();
		if(empty($reg_info)){
			$wx_data=array(
			   'wechatid'=>$this->wx_userinfo['openid'],
			   'nickname'=>$this->wx_userinfo['nickname'],
			   'headimgurl'=>$this->wx_userinfo['headimgurl'],
			   'province'=>$this->wx_userinfo['province'],
			   'city'=>$this->wx_userinfo['city'],
			   'sex'=>$this->wx_userinfo['sex'],
			   'unionid'=>$this->wx_userinfo['unionid'],				
			   'posttime'=>time()
			);
			
			if(!empty($parent_id)){
				$wx_data['p_1']=$parent_id;
				
				//父级用户信息
				$fup=M('wechat_user')->where(array('id'=>$parent_id))->find();
				$wx_data['p_1']=$parent_id;
				$wx_data['p_2']=$fup['p_1']>0?$fup['p_1']:0;
				$wx_data['p_3']=$fup['p_2']>0?$fup['p_2']:0;
				$wx_data['p_4']=$fup['p_3']>0?$fup['p_3']:0;					//上4级
				
				$wx_data['tid']=$fup['tid']>0?$fup['tid']:0;

			}
			
			if(!empty($wx_data['wechatid'])){
				$user_id=M('wechat_user')->add($wx_data);
				session('user_id',$user_id);
				$_SESSION['user_id']=$user_id;
			}
		}else{
			$wx_data=array(
			   'wechatid'=>$this->wx_userinfo['openid'],
			   'nickname'=>$this->wx_userinfo['nickname'],
			   'headimgurl'=>$this->wx_userinfo['headimgurl'],
			   'province'=>$this->wx_userinfo['province'],
			   'city'=>$this->wx_userinfo['city'],
			   'sex'=>$this->wx_userinfo['sex'],
			   'unionid'=>$this->wx_userinfo['unionid']
			);
			
			//file_put_contents('wxinfo.txt',var_export($this->wx_userinfo,true));
			
			session('user_id',$reg_info['id']);
			$_SESSION['user_id']=$reg_info['id'];
			//更新信息
			M('wechat_user')->where(array('id'=>$reg_info['id']))->save(array('unionid'=>$this->wx_userinfo['unionid']));
			
			
		}
			
	}
	
	
	
	/*
		公共数据初始化
	*/
	public function init(){
		//购物车商品数量
		$cart_count=cart_count();
		$this->assign('cart_count',$cart_count);
		
		//商品分类
		$this->cate_list=M('goods_category')->where(array('is_show'=>1,'fup'=>0))->order('list asc')->select();	
        $this->assign('cate_list',$this->cate_list);
		
		//站点配置信息
		$webinfo=M('cms_config')->find(1);
        $this->assign('webinfo',$webinfo);
		
		//微信关注外链
		$wechat_conf=M('wechat_config')->where(array('id'=>1))->find();
		$this->assign('sub_url',$wechat_conf['sub_url']);
		$this->assign('wechat_qrcode',$wechat_conf['qrcode']);
		
		//查询客服
		$service=M('wechat_service')->find(1);
		$this->assign('service',$service);
		
		/*if($this->user_id>0&&$this->user_info['role_id']==1){
			
			$fx_conf=M('resale_config')->where('id=1')->find();
		
			//升级分销商
			$cost_total_money=M('order_info')->where(array('uid'=>$uid))->sum('total_fee');
			if($cost_total_money>=$fx_conf['resaler_total_order']){
				do_up_resaler($this->user_id);
			}
		}*/
		
		
	}
	
	public function _empty(){
        $this->redirect('Index/index');
    }
    //验证码
    public function verify(){
        import('ORG.Util.Image');
        ob_end_clean();
        Image::buildImageVerify();
    }
	

}