<?php
//微信用户分组管理
class WxGroupAction extends PublicAction{
	
	private $wechatid;
	private $pubwechat;	//公众号信息
	private $wxhelper;

	public function _initialize(){
		parent::_initialize();
		header("content-type:text/html;charset=utf-8");
		import("@.ORG.Wxhelper");
		$this->wechatid=I('get.wechatid');
		$this->pubwechat=M('pubchatuser')->find(1);	//公众号信息
		$this->wxhelper=new Wxhelper($this->pubwechat);
	}
	/*
		分组列表
	*/
	public function index(){
		import("@.ORG.Page");
		$group_list=$this->wxhelper->list_group();
		$this->assign('group_list',$group_list);
		foreach($group_list['groups'] as $key=>$val){
			$g_list[$val['id']]=$val;
		}
		F('wx_groups',$g_list);
		$this->display();
		//dump($group_list);
	}
	/*
		新增用户组
	*/
	public function add(){
		if($this->_post()){
			$name=I('post.name');
			$return=$this->wxhelper->create_group($name);
			if(!empty($return['errcode'])){		//失败
				$this->redirect('index');
			}else{		//成功
				$this->redirect('index');
			}
			
		}
		$this->display();
	}
	/*
		编辑用户组
	*/
	public function edit(){
		if($this->_post()){
			$id=I('get.group_id');
			$name=I('post.name');
			$return=$this->wxhelper->update_group($id,$name);
			if(!empty($return['errcode'])){		//失败
				$this->redirect('index');
			}else{		//成功
				$this->redirect('index');
			}
		}
		$this->display();
	}
	/*
		分组二维码
	*/
	public function qrcode(){
		$id=I('get.id');		//分组id
		$return=$this->wxhelper->qrcode($id);
		$qrcode='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$return['ticket'];
		echo '二维码【右键"图片另存为"进行下载】'.'<hr/><center><img src="'.$qrcode.'"></center>';
	}
	

	
}