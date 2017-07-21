<?php
//系统设置
class WxusersAction extends PublicAction{
	
	private $wechatid;
	private $pubwechat;	//公众号信息

	public function _initialize(){
		parent::_initialize();
		$this->wechatid=I('get.wechatid');
		$this->pubwechat=M('pubchatuser')->find(1);	//公众号信息
	}
	public function index(){
		import("@.ORG.Page");
		
		$map=array('role_id'=>2,'parent_id'=>I('session.resaler_id'));
		
		$map['wechatid']=array('neq','');
		
		
		if($parent_id=I('get.parent_id')){
			$map['parent_id']=$parent_id;
		}
		
		
		$db=M('wechat_user');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$wxuser=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$groups=F('wx_groups');
		foreach($wxuser as $key=>$val){
			//$wxuser[$key]['redpack_amount']=$val['redpack_amount']/100;
			$wxuser[$key]['group_name']=$groups[$val['group_id']]['name'];
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('wxuser',$wxuser);
		$this->display();
	}

	public function uedit(){
		if(IS_POST){
			$w['id']=I('get.id');
			$data=$this->_post();
			M('wechat_user')->where($w)->save($data);
			$this->redirect('uedit',array('id'=>$w['id']));
		}
		$id=I('get.id');
		$urs=M('wechat_user')->find($id);
		if(!empty($urs['parent_id'])){
			$urs['parent']=M('wechat_user')->find($urs['parent_id']);
		}
		$this->assign('urs',$urs);
		$this->display();
	}
	/*
		删除微信用户信息
	*/
	public function del(){
		$user_id=I('get.id');
		if(M('wechat_user')->delete($user_id)){
			M('coupon')->where(array('user_id'=>$user_id))->delete();
			M('user_relation')->where(array('user_id'=>$user_id))->delete();
			M('score_log')->where(array('user_id'=>$user_id))->delete();
			M('order_info')->where(array('user_id'=>$user_id))->delete();
			$this->success('操作成功！');
		}
	}
	public function add(){
		if(IS_POST){
			$data=$this->_post();
			$data['rtime']=time();
			$rs=M('wechat_user')->data($data)->add();
			if($rs){
				$this->redirect('index');
			}else{
				$this->error('用户名已存在！');
			}
			
		}
		$this->display();
	}
	//红包记录
	public function list_redpack(){
		import("@.ORG.Page");
		$db=M('redpack_record');
		$userinfo=M('wechat_user')->find(I('get.id'));

		$map=array('wechatid'=>$userinfo['wechatid']);
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['money']=$val['money']/100;
		}
		$show = $Page->show();
		
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	//获取用户最新微信资料
	public function get_wxinfo(){
		import("@.ORG.Wxhelper");
		$helper=new Wxhelper($this->pubwechat);
		$db=M('wechat_user');
		$uid=I('get.id');
		//查询用户信息
		$info=$db->find($uid);
		//获取用户微信资料
		$return=$helper->get_user_info($info['wechatid']);
		if($return['errcode']){
			echo "获取失败,错误信息：{errcode:{$return['errcode']},errmsg:{$return['errmsg']}}";die();
		}elseif(!empty($return['headimgurl'])){
			//下载微信头像
			import("@.ORG.Http");
			import('@.ORG.Image.ThinkImage');
			$headimg="./Data/upload/headimg/".$uid.'.jpg';
			if(!is_file($headimg)||filesize($headimg)==0){
				//下载图片
				Http::curlDownload($return['headimgurl'],$headimg);
				$return['headimgurl']=$headimg;
			}
			//保存用户最新微信资料
			$wxdata=array(
				'subscribe'=>$return['subscribe'],
				'nickname'=>$return['nickname'],
				'sex'=>$return['sex'],
				'city'=>$return['city'],
				'province'=>$return['province'],
				'headimgurl'=>$headimg,//$return['headimgurl'],
				'subscribe_time'=>$return['subscribe_time'],
			);
			$db->where(array('id'=>$uid))->save($wxdata);
			$href=U('index',array('p'=>I('get.p',1),'group_id'=>I('get.group_id')));
			echo "获取微信资料成功，请<a href='$href'>刷新</a>页面查看！";
		}else{
			echo "获取微信资料成功！";
		}
	}
	//拉取粉丝列表
	public function list_wxfans(){
		import('@.ORG.Wxhelper');
		$helper=new Wxhelper($this->pubwechat);
		$next_openid=I('post.next_openid');
		$list=$helper->get_wxfans($next_openid);
		$this->assign('list',$list);
		$this->display();
	}
	//粉丝统计分析
	public function fans_analyze(){
		import("@.ORG.Wxhelper");
		$helper=new Wxhelper($this->pubwechat);
		$list=$helper->get_wxfans();
		$this->assign('list',$list);
		$month=date('m',time());
		$day=date('d',time());
		$year=date('Y',time());
		$today=mktime(0,0,0,$month,$day,$year);
		$today_sub_num=M('wechat_user')->where(array('subscribe_time'=>array('gt',$today)))->count();
		$this->assign('today_sub_num',$today_sub_num);	//今日关注人数
		
		$today_unsub_num=M('wechat_user')->where(array('subscribe'=>0,'posttime'=>array('gt',$today)))->count();
		$this->assign('today_unsub_num',$today_unsub_num);	//今日取消关注人数
		
		$this->display();	
		/*$date_range=array('begin_date'=>date('Y-m-d',time()-3600*24*7),'end_date'=>date('Y-m-d',time()));
		$res=$helper->getusersummary(json_encode($date_range));
		echo "<pre>";
		print_r($res);*/
	}
	
	/*
		资金明细
	*/
	public function money_detail(){
		import("@.ORG.Page");
		$db=M('score_log');
		$user_id=I('get.user_id');
		$user_info=M('wechat_user')->find($user_id);
		$this->assign('user_info',$user_info);
		$map=array('user_id'=>$user_id);
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$show = $Page->show();
		$this->assign('page',$show);
		
		$list=$db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$order=M('order_info')->find($val['order_id']);
			$buyer=M('wechat_user')->find($val['buyer_id']);
			$list[$key]['order_sn']=$order['order_sn'];
			$list[$key]['buyer_id']=$buyer['id'];
			$list[$key]['buyer_nickname']=$buyer['nickname'];
			$list[$key]['buyer_name']=$buyer['name'];
		}
		$this->assign('list',$list);
		$this->display();
	}
}