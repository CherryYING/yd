<?php
//系统设置
class WxusersAction extends PublicAction{
	
	private $wechatid;
	private $pubwechat;	//公众号信息

	public function _initialize(){
		parent::_initialize();
		import("@.ORG.Page");
		$this->wechatid=I('get.wechatid');
		$this->pubwechat=M('wechat_config')->find(1);	//公众号信息
	}
	public function index(){
		$db=M('wechat_user');
		
		if($role_id=I('get.role_id')){
			$map['role_id']=$role_id;
		}
		$role=I('get.role');
		if($role=='agent'){
			$map['role_id']=array('egt',2);
		}
		
		if($status=I('get.status')){
			$map['status']=$status;
		}
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		$begin_time=strtotime(I('get.begin_time'));
		$end_time=strtotime(I('get.end_time'));
		
		if(in_array($so_key,array('id','nickname','mobile','username'))){
			if(!empty($so_val)&&!empty($so_val)){
				if($so_key=='id'&&is_numeric($so_val)){
					$map[$so_key]=$so_val;
				}else{
					$map[$so_key]=array('like','%'.$so_val.'%');
				}
				
			}
		}
		
		if($begin_time>0){
			$map['posttime']=array('egt',$begin_time);
		}
		
		if($end_time>0){
			$map['posttime']=array('elt',$end_time);
		}
		
		

		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();

		foreach($list as $key=>$val){
			$where="p_1={$val['id']} or p_2={$val['id']} or p_3={$val['id']}";
			$list[$key]['son_count']=$db->where($where)->count();
			$list[$key]['order_total_fee']=M('order_info')->where(array('uid'=>$val['id'],'pay_status'=>1))->sum('total_fee');
			$list[$key]['qrcode']="./Data/QR/qrcode/".$val['id'].".jpg";
			$list[$key]['qrcard']="./Data/QR/qrcard/".$val['id'].".jpg";
			//$list[$key]['headimgurl']="./Data/upload/headimg/".$val['id']."_100x100.jpg";
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}

	public function edit(){
		$id=I('get.id');
		$p=I('post.p',1);			//页码
		
		$info=M('wechat_user')->find($id);
		
		if(empty($info)){
			$this->error('用户信息不存在！');
		}
		
		$this->assign('info',$info);
		//用户列表
		//$map=array('id'=>array('neq',$id));
		//$user_list=M('wechat_user')->where($map)->field('id,p_1,p_2,p_3,nickname,name')->select();
		foreach($user_list as $key=>$val){
			if($val['id']==$info['p_1']){
				unset($user_list[$key]);
			}
		}
		$this->assign('user_list',$user_list);
		
		//资金明细
		$map=array('uid'=>$info['id']);
		$count = M('money_water')->where($map)->count();
		$Page = new Page($count,10);
		$money_list=M('money_water')->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($money_list as $key=>$val){
			if(!empty($val['order_id'])){
				if($val['money_type']=='a'){
					$money_list[$key]['order']=M('agent_order')->where(array('id'=>$val['order_id']))->find();
				}else{
					$money_list[$key]['order']=M('order_info')->where(array('id'=>$val['order_id']))->find();
				}
				
			}
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('money_list',$money_list);
		
		if($data=$this->_post()){
			$w['id']=I('get.id');
			M('wechat_user')->where($w)->save($data);
			//$this->redirect('index',array('p'=>$p));
			$this->success('保存成功',U('edit',array('id'=>$w['id'])));
		}else{
			$this->display();	
		}
	}
	/*
		删除微信用户信息
	*/
	public function del(){
		$user_id=I('get.id');
		if(M('wechat_user')->delete($user_id)){
			/*M('coupon')->where(array('user_id'=>$user_id))->delete();
			M('user_relation')->where(array('user_id'=>$user_id))->delete();
			M('score_log')->where(array('user_id'=>$user_id))->delete();
			M('order_info')->where(array('user_id'=>$user_id))->delete();*/
			$this->success('操作成功！');
			$this->redirect('index');
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

	//获取用户最新微信资料
	public function update_wxinfo(){
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
			$icon="./Data/upload/headimg/".$uid.'_100x100.jpg';
			
			if(!is_file($headimg)||filesize($headimg)==0){
				//下载图片
				Http::curlDownload($return['headimgurl'],$headimg);
				
				if(is_file($headimg&&filesize($headimg)>0)){
					$img = new ThinkImage(THINKIMAGE_GD,$headimg); 
					$img->thumb(100,100,THINKIMAGE_THUMB_FIXED)->save($icon);
				}
				
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

			echo "获取微信资料成功，请<a href='javascript:location.reload();'>刷新</a>页面查看！";
		}else{
			echo "获取微信资料成功！";
		}
	}
	
	
	/*
		修改密码
	*/
	public function pwd(){
		$db=M('wechat_user');
		$id=I('get.id');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$data['password']=md5($arr['password']);
			$db->where(array('id'=>$id))->save($data);
			$this->success('密码修改成功！');
		}else{
			$this->display();	
		}
		
	}
	
	/*
		用户管理【上下线用户】
	*/
	public function user_relation(){
		$db=M('wechat_user');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		$this->assign('info',$info);
		//上级用户
		$parent=$db->where(array('id'=>array('in',array($info['p_1'],$info['p_2'],$info['p_3']))))->select();
		foreach($parent as $key=>$val){
			if($val['id']==$info['p_1']){
				$parent[$key]['relation']='上一级';
			}	
			if($val['id']==$info['p_2']){
				$parent[$key]['relation']='上二级';
			}	
			if($val['id']==$info['p_3']){
				$parent[$key]['relation']='上三级';
			}		
		}
		$this->assign('parent',$parent);
		
		
		//下级用户
		$map="p_1=$id or p_2=$id or p_3=$id";
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		
		$show = $Page->show();
		$this->assign('show',$show);
		
		$son_list=$db->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id asc')->select();
		foreach($son_list as $key=>$val){
			if($val['p_1']==$id){
				$son_list[$key]['relation']='下一级';
			}elseif($val['p_2']==$id){
				$son_list[$key]['relation']='下二级';
			}elseif($val['p_3']==$id){
				$son_list[$key]['relation']='下三级';
			}
		}
		$this->assign('son_list',$son_list);
		$this->display();
	}
	
	
	/*
		修改账号状态
	*/
	public function user_status(){
		$db=M('wechat_user');	
		if($id=I('get.id')){
			$user=$db->where(array('id'=>$id))->find();
			if($user['status']==1){
				$data['status']=2;
			}else{
				$data['status']=1;
			}
			$db->where(array('id'=>$id))->save($data);
			$this->success('操作成功');
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
		删除个人二维码
	*/
	public function del_qrcode(){
		$id=I('get.id');
		$qr="./Data/QR/qrcode/".$id.".jpg";
		unlink($qr);
		echo "删除成功，请<a href='javascript:location.reload();'>刷新</a>页面查看！";
	}
	/*
		删除个人二维码
	*/
	public function del_qrcard(){
		$id=I('get.id');
		$qr="./Data/QR/qrcard/".$id.".jpg";
		unlink($qr);
		echo "删除成功，请<a href='javascript:location.reload();'>刷新</a>页面查看！";
	}
	
	/*
		删除个人头像
	*/
	public function del_headimg(){
		$id=I('get.id');
		$headimg="./Data/upload/headimg/".$id.".jpg";
		$headimg100="./Data/upload/headimg/".$id."_100x100.jpg";
		unlink($headimg);
		unlink($headimg100);
		echo "删除成功，请<a href='javascript:location.reload();'>刷新</a>页面查看！";
	}
	

}